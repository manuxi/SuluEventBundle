<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Import\Http;

use Manuxi\SuluEventBundle\Import\Http\FetchException;
use Manuxi\SuluEventBundle\Import\Http\ImportHttpClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class ImportHttpClientTest extends TestCase
{
    private const AGENT = 'SuluEventBundle-Import (+https://example.com/contact)';

    /** @var list<array{method: string, url: string, headers: array<string, string>}> */
    private array $requests = [];

    /** @var list<int> microseconds the client waited */
    private array $sleeps = [];

    private float $time = 1000.0;

    protected function setUp(): void
    {
        $this->requests = [];
        $this->sleeps   = [];
        $this->time     = 1000.0;
    }

    public function testFetchesAPageAfterCheckingRobotsTxt(): void
    {
        $client = $this->createClient(static fn (string $url): MockResponse => str_ends_with($url, '/robots.txt')
            ? new MockResponse("User-agent: *\nDisallow: /private/\n")
            : new MockResponse('BEGIN:VCALENDAR', ['response_headers' => ['content-type: text/calendar']]));

        $result = $client->get('https://example.com/feed.ics');

        $this->assertSame('BEGIN:VCALENDAR', $result->body);
        $this->assertSame('text/calendar', $result->contentType);
        $this->assertFalse($result->fromCache);
        $this->assertSame(['https://example.com/robots.txt', 'https://example.com/feed.ics'], array_column($this->requests, 'url'));
    }

    public function testDisallowedPageIsNotFetched(): void
    {
        $client = $this->createClient(static fn (string $url): MockResponse => str_ends_with($url, '/robots.txt')
            ? new MockResponse("User-agent: *\nDisallow: /private/\n")
            : new MockResponse('secret'));

        try {
            $client->get('https://example.com/private/feed.ics');
            $this->fail('A page that robots.txt forbids must not be fetched.');
        } catch (FetchException $e) {
            $this->assertStringContainsString('robots.txt', $e->getMessage());
        }

        $this->assertSame(['https://example.com/robots.txt'], array_column($this->requests, 'url'));
    }

    public function testMissingRobotsTxtAllowsEverything(): void
    {
        $client = $this->createClient(static fn (string $url): MockResponse => str_ends_with($url, '/robots.txt')
            ? new MockResponse('not found', ['http_code' => 404])
            : new MockResponse('page'));

        $this->assertSame('page', $client->get('https://example.com/events')->body);
    }

    public function testBrokenRobotsTxtKeepsUsAway(): void
    {
        $client = $this->createClient(static fn (string $url): MockResponse => str_ends_with($url, '/robots.txt')
            ? new MockResponse('oops', ['http_code' => 503])
            : new MockResponse('page'));

        $this->expectException(FetchException::class);
        $this->expectExceptionMessage('robots.txt');

        $client->get('https://example.com/events');
    }

    public function testRobotsTxtIsKeptFor24Hours(): void
    {
        $client = $this->createClient(static fn (string $url): MockResponse => str_ends_with($url, '/robots.txt') ? new MockResponse('') : new MockResponse('page'));

        $client->get('https://example.com/a');
        $client->get('https://example.com/b');

        $this->assertCount(1, array_filter($this->requests, static fn (array $request): bool => str_ends_with($request['url'], '/robots.txt')));
    }

    public function testUnchangedPageIsAskedWithETagAndServedFromCache(): void
    {
        $page   = 0;
        $client = $this->createClient(static function (string $url, array $options) use (&$page): MockResponse {
            if (str_ends_with($url, '/robots.txt')) {
                return new MockResponse('');
            }

            ++$page;
            if (1 === $page) {
                return new MockResponse('first body', ['response_headers' => ['ETag: "abc"', 'Last-Modified: Mon, 01 Mar 2027 10:00:00 GMT']]);
            }

            return new MockResponse('', ['http_code' => 304]);
        });

        $first  = $client->get('https://example.com/feed.ics');
        $second = $client->get('https://example.com/feed.ics');

        $this->assertFalse($first->fromCache);
        $this->assertTrue($second->fromCache);
        $this->assertSame('first body', $second->body);

        $conditional = end($this->requests);
        $this->assertSame('"abc"', $conditional['headers']['if-none-match'] ?? null);
        $this->assertSame('Mon, 01 Mar 2027 10:00:00 GMT', $conditional['headers']['if-modified-since'] ?? null);
    }

    public function testChangedPageReplacesTheCachedOne(): void
    {
        $bodies = ['one', 'two'];
        $client = $this->createClient(static function (string $url) use (&$bodies): MockResponse {
            return str_ends_with($url, '/robots.txt') ? new MockResponse('') : new MockResponse(array_shift($bodies), ['response_headers' => ['ETag: "' . \count($bodies) . '"']]);
        });

        $client->get('https://example.com/feed.ics');

        $this->assertSame('two', $client->get('https://example.com/feed.ics')->body);
    }

    public function testErrorStatusIsAnError(): void
    {
        $client = $this->createClient(static fn (string $url): MockResponse => str_ends_with($url, '/robots.txt') ? new MockResponse('') : new MockResponse('nope', ['http_code' => 500]));

        $this->expectException(FetchException::class);
        $this->expectExceptionMessage('HTTP 500');

        $client->get('https://example.com/feed.ics');
    }

    public function testNetworkErrorIsAFetchException(): void
    {
        $client = $this->createClient(static function (string $url): MockResponse {
            if (str_ends_with($url, '/robots.txt')) {
                return new MockResponse('');
            }

            throw new TransportException('Connection refused');
        });

        $this->expectException(FetchException::class);
        $this->expectExceptionMessage('Connection refused');

        $client->get('https://example.com/feed.ics');
    }

    public function testOnlyHttpUrlsAreFetched(): void
    {
        $client = $this->createClient(static fn (): MockResponse => new MockResponse(''));

        $this->expectException(FetchException::class);
        $this->expectExceptionMessage('not an http(s) url');

        $client->get('file:///etc/passwd');
    }

    public function testTooLargeResponseIsRefused(): void
    {
        $client = $this->createClient(static fn (string $url): MockResponse => str_ends_with($url, '/robots.txt')
            ? new MockResponse('')
            : new MockResponse(str_repeat('x', 10 * 1024 * 1024 + 1)));

        $this->expectException(FetchException::class);
        $this->expectExceptionMessage('larger than');

        $client->get('https://example.com/huge.ics');
    }

    public function testAtMostOneRequestPerSecondAndHost(): void
    {
        $client = $this->createClient(static fn (string $url): MockResponse => str_ends_with($url, '/robots.txt') ? new MockResponse('') : new MockResponse('page'));

        $client->get('https://example.com/a');
        $client->get('https://example.com/b');
        $client->get('https://example.com/c');

        // robots.txt, a, b, c: every request after the first waits for the second to pass
        $this->assertCount(3, $this->sleeps);
        foreach ($this->sleeps as $microseconds) {
            $this->assertEqualsWithDelta(1_000_000, $microseconds, 1000);
        }
    }

    public function testDifferentHostsDoNotWaitForEachOther(): void
    {
        $client = $this->createClient(static fn (string $url): MockResponse => str_ends_with($url, '/robots.txt') ? new MockResponse('') : new MockResponse('page'));

        $client->get('https://one.example.com/a');
        $client->get('https://two.example.org/a');

        // only the page after the robots.txt of the same host waits
        $this->assertCount(2, $this->sleeps);
    }

    public function testUserAgentComesFromTheConfiguration(): void
    {
        $client = $this->createClient(static fn (): MockResponse => new MockResponse(''));

        $this->assertSame(self::AGENT, $client->getUserAgent());
    }

    private function createClient(callable $respond): ImportHttpClient
    {
        $mock = new MockHttpClient(function (string $method, string $url, array $options) use ($respond): MockResponse {
            $headers = [];
            foreach ($options['normalized_headers'] ?? [] as $name => $values) {
                $headers[$name] = ltrim(mb_substr((string) $values[0], mb_strlen($name) + 1));
            }
            $this->requests[] = ['method' => $method, 'url' => $url, 'headers' => $headers];

            return $respond($url, $options);
        });

        return new ImportHttpClient(
            $mock,
            new ArrayAdapter(),
            self::AGENT,
            15.0,
            1.0,
            function (int $microseconds): void {
                $this->sleeps[] = $microseconds;
                $this->time += $microseconds / 1_000_000;
            },
            fn (): float => $this->time,
        );
    }
}
