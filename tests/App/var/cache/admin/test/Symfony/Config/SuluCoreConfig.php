<?php

namespace Symfony\Config;

require_once __DIR__.\DIRECTORY_SEPARATOR.'SuluCore'.\DIRECTORY_SEPARATOR.'WebspaceConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'SuluCore'.\DIRECTORY_SEPARATOR.'FieldsDefaultsConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'SuluCore'.\DIRECTORY_SEPARATOR.'CacheConfig.php';

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Loader\ParamConfigurator;

/**
 * This class is automatically generated to help in creating a config.
 */
class SuluCoreConfig implements \Symfony\Component\Config\Builder\ConfigBuilderInterface
{
    private $webspace;
    private $fieldsDefaults;
    private $cacheDir;
    private $cache;
    private $locales;
    private $translations;
    private $fallbackLocale;
    private $_usedProperties = [];
    private $_hasDeprecatedCalls = false;

    /**
     * @default {"config_dir":"%kernel.project_dir%\/config\/webspaces"}
     * @deprecated since Symfony 7.4
     */
    public function webspace(array $value = []): \Symfony\Config\SuluCore\WebspaceConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (null === $this->webspace) {
            $this->_usedProperties['webspace'] = true;
            $this->webspace = new \Symfony\Config\SuluCore\WebspaceConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "webspace()" has already been initialized. You cannot pass values the second time you call webspace().');
        }

        return $this->webspace;
    }

    /**
     * @default {"translations":{"id":"public.id","title":"public.title","name":"public.name","created":"public.created","changed":"public.changed"},"widths":{"id":"50px"}}
     * @deprecated since Symfony 7.4
     */
    public function fieldsDefaults(array $value = []): \Symfony\Config\SuluCore\FieldsDefaultsConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (null === $this->fieldsDefaults) {
            $this->_usedProperties['fieldsDefaults'] = true;
            $this->fieldsDefaults = new \Symfony\Config\SuluCore\FieldsDefaultsConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "fieldsDefaults()" has already been initialized. You cannot pass values the second time you call fieldsDefaults().');
        }

        return $this->fieldsDefaults;
    }

    /**
     * @default '%kernel.cache_dir%/sulu'
     * @param ParamConfigurator|mixed $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function cacheDir($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['cacheDir'] = true;
        $this->cacheDir = $value;

        return $this;
    }

    /**
     * @default {"memoize":{"default_lifetime":1}}
     * @deprecated since Symfony 7.4
     */
    public function cache(array $value = []): \Symfony\Config\SuluCore\CacheConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (null === $this->cache) {
            $this->_usedProperties['cache'] = true;
            $this->cache = new \Symfony\Config\SuluCore\CacheConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "cache()" has already been initialized. You cannot pass values the second time you call cache().');
        }

        return $this->cache;
    }

    /**
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function locales(string $locale, mixed $value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['locales'] = true;
        $this->locales[$locale] = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed> $value
     *
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function translations(ParamConfigurator|array $value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['translations'] = true;
        $this->translations = $value;

        return $this;
    }

    /**
     * @default 'en'
     * @param ParamConfigurator|mixed $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function fallbackLocale($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['fallbackLocale'] = true;
        $this->fallbackLocale = $value;

        return $this;
    }

    public function getExtensionAlias(): string
    {
        return 'sulu_core';
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('webspace', $config)) {
            $this->_usedProperties['webspace'] = true;
            $this->webspace = new \Symfony\Config\SuluCore\WebspaceConfig($config['webspace']);
            unset($config['webspace']);
        }

        if (array_key_exists('fields_defaults', $config)) {
            $this->_usedProperties['fieldsDefaults'] = true;
            $this->fieldsDefaults = new \Symfony\Config\SuluCore\FieldsDefaultsConfig($config['fields_defaults']);
            unset($config['fields_defaults']);
        }

        if (array_key_exists('cache_dir', $config)) {
            $this->_usedProperties['cacheDir'] = true;
            $this->cacheDir = $config['cache_dir'];
            unset($config['cache_dir']);
        }

        if (array_key_exists('cache', $config)) {
            $this->_usedProperties['cache'] = true;
            $this->cache = new \Symfony\Config\SuluCore\CacheConfig($config['cache']);
            unset($config['cache']);
        }

        if (array_key_exists('locales', $config)) {
            $this->_usedProperties['locales'] = true;
            $this->locales = $config['locales'];
            unset($config['locales']);
        }

        if (array_key_exists('translations', $config)) {
            $this->_usedProperties['translations'] = true;
            $this->translations = $config['translations'];
            unset($config['translations']);
        }

        if (array_key_exists('fallback_locale', $config)) {
            $this->_usedProperties['fallbackLocale'] = true;
            $this->fallbackLocale = $config['fallback_locale'];
            unset($config['fallback_locale']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['webspace'])) {
            $output['webspace'] = $this->webspace->toArray();
        }
        if (isset($this->_usedProperties['fieldsDefaults'])) {
            $output['fields_defaults'] = $this->fieldsDefaults->toArray();
        }
        if (isset($this->_usedProperties['cacheDir'])) {
            $output['cache_dir'] = $this->cacheDir;
        }
        if (isset($this->_usedProperties['cache'])) {
            $output['cache'] = $this->cache->toArray();
        }
        if (isset($this->_usedProperties['locales'])) {
            $output['locales'] = $this->locales;
        }
        if (isset($this->_usedProperties['translations'])) {
            $output['translations'] = $this->translations;
        }
        if (isset($this->_usedProperties['fallbackLocale'])) {
            $output['fallback_locale'] = $this->fallbackLocale;
        }
        if ($this->_hasDeprecatedCalls) {
            trigger_deprecation('symfony/config', '7.4', 'Calling any fluent method on "%s" is deprecated; pass the configuration to the constructor instead.', $this::class);
        }

        return $output;
    }

}
