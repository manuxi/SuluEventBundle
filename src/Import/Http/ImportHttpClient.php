<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Import\Http;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Fetches pages for the import, politely: with an own user agent, only what robots.txt allows (the answer is kept for
 * 24 hours), at most one request per second and host, and with ETag / Last-Modified so an unchanged page costs next to
 * nothing.
 */
class ImportHttpClient
{
    private const ROBOTS_LIFETIME   = 86400;
    private const RESPONSE_LIFETIME = 2592000;
    private const MAX_BYTES         = 10 * 1024 * 1024;

    /** @var array<string, float> host => time of the last request */
    private array $lastRequest = [];

    /** @var \Closure(int): void */
    private \Closure $sleep;

    /** @var \Closure(): float */
    private \Closure $clock;

    /**
     * @param (\Closure(int): void)|null $sleep waits the given microseconds (replaceable for tests)
     * @param (\Closure(): float)|null   $clock current time in seconds with fraction (replaceable for tests)
     */
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly CacheItemPoolInterface $cache,
        private readonly string $userAgent,
        private readonly float $timeout = 15.0,
        private readonly float $minInterval = 1.0,
        ?\Closure $sleep = null,
        ?\Closure $clock = null,
    ) {
        $this->sleep = $sleep ?? static function (int $microseconds): void {
            usleep($microseconds);
        };
        $this->clock = $clock ?? static fn (): float => microtime(true);
    }

    public function getUserAgent(): string
    {
        return $this->userAgent;
    }

    /**
     * @throws FetchException
     */
    public function get(string $url): FetchResult
    {
        $parts  = parse_url($url);
        $scheme = mb_strtolower((string) ($parts['scheme'] ?? ''));
        $host   = mb_strtolower((string) ($parts['host'] ?? ''));
        if (!\in_array($scheme, ['http', 'https'], true) || '' === $host) {
            throw new FetchException(\sprintf('"%s" is not an http(s) url.', $url));
        }

        $pathAndQuery = ($parts['path'] ?? '/') . (isset($parts['query']) ? '?' . $parts['query'] : '');
        if (!$this->getRobots($scheme, $host, isset($parts['port']) ? (int) $parts['port'] : null)->isAllowed($pathAndQuery)) {
            throw new FetchException(\sprintf('robots.txt of %s does not allow to fetch %s.', $host, $pathAndQuery));
        }

        $cacheKey = 'response.' . sha1($url);
        $item     = $this->cache->getItem($cacheKey);
        /** @var array{etag?: string|null, lastModified?: string|null, body: string, contentType?: string|null}|null $cached */
        $cached = $item->isHit() ? $item->get() : null;

        $headers = [];
        if (null !== $cached) {
            if (!empty($cached['etag'])) {
                $headers['If-None-Match'] = $cached['etag'];
            }
            if (!empty($cached['lastModified'])) {
                $headers['If-Modified-Since'] = $cached['lastModified'];
            }
        }

        try {
            $this->waitForTurn($host);
            $response = $this->httpClient->request('GET', $url, [
                'headers'       => $headers + ['Accept' => 'text/calendar, text/html;q=0.9, application/ld+json;q=0.8, application/json;q=0.7, */*;q=0.5'],
                'timeout'       => $this->timeout,
                'max_duration'  => $this->timeout * 4,
                'max_redirects' => 5,
            ]);
            $status          = $response->getStatusCode();
            $responseHeaders = $response->getHeaders(false);

            if (304 === $status && null !== $cached) {
                return new FetchResult($url, $cached['body'], $cached['contentType'] ?? null, true);
            }

            if ($status >= 400) {
                throw new FetchException(\sprintf('%s answered with HTTP %d.', $url, $status));
            }

            if ((int) ($responseHeaders['content-length'][0] ?? 0) > self::MAX_BYTES) {
                throw new FetchException(\sprintf('%s is larger than %d MB.', $url, self::MAX_BYTES / 1024 / 1024));
            }

            $body = $response->getContent(false);
        } catch (ExceptionInterface $e) {
            throw new FetchException(\sprintf('%s could not be fetched: %s', $url, $e->getMessage()), 0, $e);
        }

        if (mb_strlen($body) > self::MAX_BYTES) {
            throw new FetchException(\sprintf('%s is larger than %d MB.', $url, self::MAX_BYTES / 1024 / 1024));
        }

        $contentType  = $responseHeaders['content-type'][0]  ?? null;
        $etag         = $responseHeaders['etag'][0]          ?? null;
        $lastModified = $responseHeaders['last-modified'][0] ?? null;
        if (null !== $etag || null !== $lastModified) {
            $item->set(['etag' => $etag, 'lastModified' => $lastModified, 'body' => $body, 'contentType' => $contentType]);
            $item->expiresAfter(self::RESPONSE_LIFETIME);
            $this->cache->save($item);
        }

        return new FetchResult($url, $body, $contentType);
    }

    private function getRobots(string $scheme, string $host, ?int $port): RobotsTxt
    {
        $key  = 'robots.' . sha1($scheme . '://' . $host . (null !== $port ? ':' . $port : ''));
        $item = $this->cache->getItem($key);
        if ($item->isHit()) {
            /** @var array{status: int, body: string} $stored */
            $stored = $item->get();

            return $this->createRobots($stored['status'], $stored['body']);
        }

        $robotsUrl = $scheme . '://' . $host . (null !== $port ? ':' . $port : '') . '/robots.txt';
        $status    = 0;
        $body      = '';
        try {
            $this->waitForTurn($host);
            $response = $this->httpClient->request('GET', $robotsUrl, [
                'headers'       => ['Accept' => 'text/plain, */*;q=0.1'],
                'timeout'       => $this->timeout,
                'max_redirects' => 5,
            ]);
            $status = $response->getStatusCode();
            $body   = $status < 400 ? mb_substr($response->getContent(false), 0, 512 * 1024) : '';
        } catch (ExceptionInterface) {
            // not reachable: handled as "unknown" below, and not kept for 24 hours
            return RobotsTxt::disallowAll();
        }

        $item->set(['status' => $status, 'body' => $body]);
        $item->expiresAfter(self::ROBOTS_LIFETIME);
        $this->cache->save($item);

        return $this->createRobots($status, $body);
    }

    private function createRobots(int $status, string $body): RobotsTxt
    {
        // 4xx: there is no robots.txt, everything is allowed. 5xx: the site is in trouble, stay away.
        if ($status >= 500) {
            return RobotsTxt::disallowAll();
        }

        return $status >= 400 ? RobotsTxt::allowAll() : new RobotsTxt($body, $this->userAgent);
    }

    private function waitForTurn(string $host): void
    {
        $now  = ($this->clock)();
        $next = ($this->lastRequest[$host] ?? 0.0) + $this->minInterval;
        if ($next > $now) {
            ($this->sleep)((int) round(($next - $now) * 1_000_000));
            $now = $next;
        }

        $this->lastRequest[$host] = $now;
    }
}
