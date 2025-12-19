<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Functional;

use Manuxi\SuluEventBundle\Repository\EventRepository;
use Manuxi\SuluEventBundle\Sitemap\EventSitemapProvider;
use Manuxi\SuluEventBundle\Tests\App\Kernel;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SuluEventBundleInitializationTest extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        static::ensureKernelShutdown();
        restore_exception_handler();
    }

    public function testContainerServices(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        // Check EventRepository service
        $repoId = EventRepository::class;
        $this->assertTrue($container->has($repoId), "Container should have service $repoId");
        $repository = $container->get($repoId);
        $this->assertInstanceOf(EventRepository::class, $repository);

        // Check SitemapProvider service
        // Defined as 'sulu_event.sitemap_provider' in services.yaml
        $sitemapId = 'sulu_event.sitemap_provider';
        $this->assertTrue($container->has($sitemapId), "Container should have service $sitemapId");
        $sitemapProvider = $container->get($sitemapId);
        $this->assertInstanceOf(EventSitemapProvider::class, $sitemapProvider);

        // Verify that the bundle is registered in the kernel
        $bundles = self::$kernel->getBundles();
        $this->assertArrayHasKey('SuluEventBundle', $bundles);
    }
}
