<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Dashboard;

use PHPUnit\Framework\TestCase;

/**
 * manuxi/sulu-dashboard-bundle is only suggested, not installed with this bundle. To run the tests of the tiles, point
 * SULU_DASHBOARD_BUNDLE_PATH to a checkout of it:
 *
 *     SULU_DASHBOARD_BUNDLE_PATH=/path/to/SuluDashboardBundle vendor/bin/phpunit tests/Unit/Dashboard
 *
 * Without it (or without an installed bundle) the tests are skipped.
 */
abstract class DashboardTestCase extends TestCase
{
    private const WIDGET_INTERFACE = 'Manuxi\\SuluDashboardBundle\\Widget\\DashboardWidgetInterface';

    protected function setUp(): void
    {
        parent::setUp();

        if (!interface_exists(self::WIDGET_INTERFACE)) {
            $path = getenv('SULU_DASHBOARD_BUNDLE_PATH');

            if (\is_string($path) && '' !== $path) {
                spl_autoload_register(static function (string $class) use ($path): void {
                    $prefix = 'Manuxi\\SuluDashboardBundle\\';

                    if (str_starts_with($class, $prefix)) {
                        $file = rtrim($path, '/\\') . '/src/' . str_replace('\\', '/', substr($class, \strlen($prefix))) . '.php';

                        if (is_file($file)) {
                            require_once $file;
                        }
                    }
                });
            }
        }

        if (!interface_exists(self::WIDGET_INTERFACE)) {
            $this->markTestSkipped('manuxi/sulu-dashboard-bundle is not available (set SULU_DASHBOARD_BUNDLE_PATH).');
        }
    }
}
