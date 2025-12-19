<?php

namespace Symfony\Config;

require_once __DIR__.\DIRECTORY_SEPARATOR.'SuluWebsite'.\DIRECTORY_SEPARATOR.'AnalyticsConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'SuluWebsite'.\DIRECTORY_SEPARATOR.'SegmentsConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'SuluWebsite'.\DIRECTORY_SEPARATOR.'TwigConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'SuluWebsite'.\DIRECTORY_SEPARATOR.'SitemapConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'SuluWebsite'.\DIRECTORY_SEPARATOR.'DefaultLocaleConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'SuluWebsite'.\DIRECTORY_SEPARATOR.'ObjectsConfig.php';

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class SuluWebsiteConfig implements \Symfony\Component\Config\Builder\ConfigBuilderInterface
{
    private $analytics;
    private $segments;
    private $twig;
    private $sitemap;
    private $defaultLocale;
    private $objects;
    private $_usedProperties = [];
    private $_hasDeprecatedCalls = false;

    /**
     * @template TValue of array|bool
     * @param TValue $value
     * @default {"enabled":true}
     * @return \Symfony\Config\SuluWebsite\AnalyticsConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\SuluWebsite\AnalyticsConfig : static)
     * @deprecated since Symfony 7.4
     */
    public function analytics(array|bool $value = []): \Symfony\Config\SuluWebsite\AnalyticsConfig|static
    {
        $this->_hasDeprecatedCalls = true;
        if (!\is_array($value)) {
            $this->_usedProperties['analytics'] = true;
            $this->analytics = $value;

            return $this;
        }

        if (!$this->analytics instanceof \Symfony\Config\SuluWebsite\AnalyticsConfig) {
            $this->_usedProperties['analytics'] = true;
            $this->analytics = new \Symfony\Config\SuluWebsite\AnalyticsConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "analytics()" has already been initialized. You cannot pass values the second time you call analytics().');
        }

        return $this->analytics;
    }

    /**
     * @default {"switch_url":"\/_sulu_segment_switch","cookie":"_ss","header":"X-Sulu-Segment"}
     * @deprecated since Symfony 7.4
     */
    public function segments(array $value = []): \Symfony\Config\SuluWebsite\SegmentsConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (null === $this->segments) {
            $this->_usedProperties['segments'] = true;
            $this->segments = new \Symfony\Config\SuluWebsite\SegmentsConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "segments()" has already been initialized. You cannot pass values the second time you call segments().');
        }

        return $this->segments;
    }

    /**
     * @default {"navigation":{"cache_lifetime":1},"content":{"cache_lifetime":1},"sitemap":{"cache_lifetime":3600}}
     * @deprecated since Symfony 7.4
     */
    public function twig(array $value = []): \Symfony\Config\SuluWebsite\TwigConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (null === $this->twig) {
            $this->_usedProperties['twig'] = true;
            $this->twig = new \Symfony\Config\SuluWebsite\TwigConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "twig()" has already been initialized. You cannot pass values the second time you call twig().');
        }

        return $this->twig;
    }

    /**
     * @default {"dump_dir":"%sulu.cache_dir%\/sitemaps"}
     * @deprecated since Symfony 7.4
     */
    public function sitemap(array $value = []): \Symfony\Config\SuluWebsite\SitemapConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (null === $this->sitemap) {
            $this->_usedProperties['sitemap'] = true;
            $this->sitemap = new \Symfony\Config\SuluWebsite\SitemapConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "sitemap()" has already been initialized. You cannot pass values the second time you call sitemap().');
        }

        return $this->sitemap;
    }

    /**
     * @default {"provider_service_id":"sulu_website.default_locale.portal_provider"}
     * @deprecated since Symfony 7.4
     */
    public function defaultLocale(array $value = []): \Symfony\Config\SuluWebsite\DefaultLocaleConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (null === $this->defaultLocale) {
            $this->_usedProperties['defaultLocale'] = true;
            $this->defaultLocale = new \Symfony\Config\SuluWebsite\DefaultLocaleConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "defaultLocale()" has already been initialized. You cannot pass values the second time you call defaultLocale().');
        }

        return $this->defaultLocale;
    }

    /**
     * @default {"analytics":{"model":"Sulu\\Bundle\\WebsiteBundle\\Entity\\Analytics","repository":"Sulu\\Bundle\\WebsiteBundle\\Entity\\AnalyticsRepository"}}
     * @deprecated since Symfony 7.4
     */
    public function objects(array $value = []): \Symfony\Config\SuluWebsite\ObjectsConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (null === $this->objects) {
            $this->_usedProperties['objects'] = true;
            $this->objects = new \Symfony\Config\SuluWebsite\ObjectsConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "objects()" has already been initialized. You cannot pass values the second time you call objects().');
        }

        return $this->objects;
    }

    public function getExtensionAlias(): string
    {
        return 'sulu_website';
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('analytics', $config)) {
            $this->_usedProperties['analytics'] = true;
            $this->analytics = \is_array($config['analytics']) ? new \Symfony\Config\SuluWebsite\AnalyticsConfig($config['analytics']) : $config['analytics'];
            unset($config['analytics']);
        }

        if (array_key_exists('segments', $config)) {
            $this->_usedProperties['segments'] = true;
            $this->segments = new \Symfony\Config\SuluWebsite\SegmentsConfig($config['segments']);
            unset($config['segments']);
        }

        if (array_key_exists('twig', $config)) {
            $this->_usedProperties['twig'] = true;
            $this->twig = new \Symfony\Config\SuluWebsite\TwigConfig($config['twig']);
            unset($config['twig']);
        }

        if (array_key_exists('sitemap', $config)) {
            $this->_usedProperties['sitemap'] = true;
            $this->sitemap = new \Symfony\Config\SuluWebsite\SitemapConfig($config['sitemap']);
            unset($config['sitemap']);
        }

        if (array_key_exists('default_locale', $config)) {
            $this->_usedProperties['defaultLocale'] = true;
            $this->defaultLocale = new \Symfony\Config\SuluWebsite\DefaultLocaleConfig($config['default_locale']);
            unset($config['default_locale']);
        }

        if (array_key_exists('objects', $config)) {
            $this->_usedProperties['objects'] = true;
            $this->objects = new \Symfony\Config\SuluWebsite\ObjectsConfig($config['objects']);
            unset($config['objects']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['analytics'])) {
            $output['analytics'] = $this->analytics instanceof \Symfony\Config\SuluWebsite\AnalyticsConfig ? $this->analytics->toArray() : $this->analytics;
        }
        if (isset($this->_usedProperties['segments'])) {
            $output['segments'] = $this->segments->toArray();
        }
        if (isset($this->_usedProperties['twig'])) {
            $output['twig'] = $this->twig->toArray();
        }
        if (isset($this->_usedProperties['sitemap'])) {
            $output['sitemap'] = $this->sitemap->toArray();
        }
        if (isset($this->_usedProperties['defaultLocale'])) {
            $output['default_locale'] = $this->defaultLocale->toArray();
        }
        if (isset($this->_usedProperties['objects'])) {
            $output['objects'] = $this->objects->toArray();
        }
        if ($this->_hasDeprecatedCalls) {
            trigger_deprecation('symfony/config', '7.4', 'Calling any fluent method on "%s" is deprecated; pass the configuration to the constructor instead.', $this::class);
        }

        return $output;
    }

}
