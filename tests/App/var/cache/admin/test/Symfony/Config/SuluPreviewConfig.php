<?php

namespace Symfony\Config;

require_once __DIR__.\DIRECTORY_SEPARATOR.'SuluPreview'.\DIRECTORY_SEPARATOR.'DefaultsConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'SuluPreview'.\DIRECTORY_SEPARATOR.'CacheConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'SuluPreview'.\DIRECTORY_SEPARATOR.'ObjectsConfig.php';

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Loader\ParamConfigurator;

/**
 * This class is automatically generated to help in creating a config.
 */
class SuluPreviewConfig implements \Symfony\Component\Config\Builder\ConfigBuilderInterface
{
    private $defaults;
    private $mode;
    private $delay;
    private $cacheAdapter;
    private $cache;
    private $objects;
    private $_usedProperties = [];
    private $_hasDeprecatedCalls = false;

    /**
     * @deprecated since Symfony 7.4
     */
    public function defaults(array $value = []): \Symfony\Config\SuluPreview\DefaultsConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (null === $this->defaults) {
            $this->_usedProperties['defaults'] = true;
            $this->defaults = new \Symfony\Config\SuluPreview\DefaultsConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "defaults()" has already been initialized. You cannot pass values the second time you call defaults().');
        }

        return $this->defaults;
    }

    /**
     * @default 'auto'
     * @param ParamConfigurator|mixed $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function mode($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['mode'] = true;
        $this->mode = $value;

        return $this;
    }

    /**
     * Used for the delayed send of changes
     * @default 500
     * @param ParamConfigurator|mixed $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function delay($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['delay'] = true;
        $this->delay = $value;

        return $this;
    }

    /**
     * Define the symfony framework cache adapter for preview
     * @default 'cache.app'
     * @param ParamConfigurator|mixed $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function cacheAdapter($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['cacheAdapter'] = true;
        $this->cacheAdapter = $value;

        return $this;
    }

    /**
     * @default {"type":null,"namespace":null,"file_system":{"directory":"%sulu.cache_dir%\/preview","extension":null,"umask":2},"redis":{"connection_id":null,"host":"127.0.0.1","port":"6379","password":null,"timeout":null,"database":null}}
     * @deprecated Since sulu/sulu 2.1.0: The "cache" option is deprecated. Use "cache_adapter" instead.
     * @deprecated since Symfony 7.4
     */
    public function cache(array $value = []): \Symfony\Config\SuluPreview\CacheConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (null === $this->cache) {
            $this->_usedProperties['cache'] = true;
            $this->cache = new \Symfony\Config\SuluPreview\CacheConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "cache()" has already been initialized. You cannot pass values the second time you call cache().');
        }

        return $this->cache;
    }

    /**
     * @default {"preview_link":{"model":"Sulu\\Bundle\\PreviewBundle\\Domain\\Model\\PreviewLink"}}
     * @deprecated since Symfony 7.4
     */
    public function objects(array $value = []): \Symfony\Config\SuluPreview\ObjectsConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (null === $this->objects) {
            $this->_usedProperties['objects'] = true;
            $this->objects = new \Symfony\Config\SuluPreview\ObjectsConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "objects()" has already been initialized. You cannot pass values the second time you call objects().');
        }

        return $this->objects;
    }

    public function getExtensionAlias(): string
    {
        return 'sulu_preview';
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('defaults', $config)) {
            $this->_usedProperties['defaults'] = true;
            $this->defaults = new \Symfony\Config\SuluPreview\DefaultsConfig($config['defaults']);
            unset($config['defaults']);
        }

        if (array_key_exists('mode', $config)) {
            $this->_usedProperties['mode'] = true;
            $this->mode = $config['mode'];
            unset($config['mode']);
        }

        if (array_key_exists('delay', $config)) {
            $this->_usedProperties['delay'] = true;
            $this->delay = $config['delay'];
            unset($config['delay']);
        }

        if (array_key_exists('cache_adapter', $config)) {
            $this->_usedProperties['cacheAdapter'] = true;
            $this->cacheAdapter = $config['cache_adapter'];
            unset($config['cache_adapter']);
        }

        if (array_key_exists('cache', $config)) {
            $this->_usedProperties['cache'] = true;
            $this->cache = new \Symfony\Config\SuluPreview\CacheConfig($config['cache']);
            unset($config['cache']);
        }

        if (array_key_exists('objects', $config)) {
            $this->_usedProperties['objects'] = true;
            $this->objects = new \Symfony\Config\SuluPreview\ObjectsConfig($config['objects']);
            unset($config['objects']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['defaults'])) {
            $output['defaults'] = $this->defaults->toArray();
        }
        if (isset($this->_usedProperties['mode'])) {
            $output['mode'] = $this->mode;
        }
        if (isset($this->_usedProperties['delay'])) {
            $output['delay'] = $this->delay;
        }
        if (isset($this->_usedProperties['cacheAdapter'])) {
            $output['cache_adapter'] = $this->cacheAdapter;
        }
        if (isset($this->_usedProperties['cache'])) {
            $output['cache'] = $this->cache->toArray();
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
