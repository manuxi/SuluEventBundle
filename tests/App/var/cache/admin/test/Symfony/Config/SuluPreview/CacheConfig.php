<?php

namespace Symfony\Config\SuluPreview;

require_once __DIR__.\DIRECTORY_SEPARATOR.'Cache'.\DIRECTORY_SEPARATOR.'ApcConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Cache'.\DIRECTORY_SEPARATOR.'ApcuConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Cache'.\DIRECTORY_SEPARATOR.'ArrayConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Cache'.\DIRECTORY_SEPARATOR.'FileSystemConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Cache'.\DIRECTORY_SEPARATOR.'RedisConfig.php';

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class CacheConfig 
{
    private $type;
    private $namespace;
    private $apc;
    private $apcu;
    private $array;
    private $fileSystem;
    private $redis;
    private $_usedProperties = [];

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function type($value): static
    {
        $this->_usedProperties['type'] = true;
        $this->type = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function namespace($value): static
    {
        $this->_usedProperties['namespace'] = true;
        $this->namespace = $value;

        return $this;
    }

    public function apc(array $value = []): \Symfony\Config\SuluPreview\Cache\ApcConfig
    {
        if (null === $this->apc) {
            $this->_usedProperties['apc'] = true;
            $this->apc = new \Symfony\Config\SuluPreview\Cache\ApcConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "apc()" has already been initialized. You cannot pass values the second time you call apc().');
        }

        return $this->apc;
    }

    public function apcu(array $value = []): \Symfony\Config\SuluPreview\Cache\ApcuConfig
    {
        if (null === $this->apcu) {
            $this->_usedProperties['apcu'] = true;
            $this->apcu = new \Symfony\Config\SuluPreview\Cache\ApcuConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "apcu()" has already been initialized. You cannot pass values the second time you call apcu().');
        }

        return $this->apcu;
    }

    public function array(array $value = []): \Symfony\Config\SuluPreview\Cache\ArrayConfig
    {
        if (null === $this->array) {
            $this->_usedProperties['array'] = true;
            $this->array = new \Symfony\Config\SuluPreview\Cache\ArrayConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "array()" has already been initialized. You cannot pass values the second time you call array().');
        }

        return $this->array;
    }

    /**
     * @default {"directory":"%sulu.cache_dir%\/preview","extension":null,"umask":2}
     */
    public function fileSystem(array $value = []): \Symfony\Config\SuluPreview\Cache\FileSystemConfig
    {
        if (null === $this->fileSystem) {
            $this->_usedProperties['fileSystem'] = true;
            $this->fileSystem = new \Symfony\Config\SuluPreview\Cache\FileSystemConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "fileSystem()" has already been initialized. You cannot pass values the second time you call fileSystem().');
        }

        return $this->fileSystem;
    }

    /**
     * @default {"connection_id":null,"host":"127.0.0.1","port":"6379","password":null,"timeout":null,"database":null}
     */
    public function redis(array $value = []): \Symfony\Config\SuluPreview\Cache\RedisConfig
    {
        if (null === $this->redis) {
            $this->_usedProperties['redis'] = true;
            $this->redis = new \Symfony\Config\SuluPreview\Cache\RedisConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "redis()" has already been initialized. You cannot pass values the second time you call redis().');
        }

        return $this->redis;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('type', $config)) {
            $this->_usedProperties['type'] = true;
            $this->type = $config['type'];
            unset($config['type']);
        }

        if (array_key_exists('namespace', $config)) {
            $this->_usedProperties['namespace'] = true;
            $this->namespace = $config['namespace'];
            unset($config['namespace']);
        }

        if (array_key_exists('apc', $config)) {
            $this->_usedProperties['apc'] = true;
            $this->apc = new \Symfony\Config\SuluPreview\Cache\ApcConfig($config['apc']);
            unset($config['apc']);
        }

        if (array_key_exists('apcu', $config)) {
            $this->_usedProperties['apcu'] = true;
            $this->apcu = new \Symfony\Config\SuluPreview\Cache\ApcuConfig($config['apcu']);
            unset($config['apcu']);
        }

        if (array_key_exists('array', $config)) {
            $this->_usedProperties['array'] = true;
            $this->array = new \Symfony\Config\SuluPreview\Cache\ArrayConfig($config['array']);
            unset($config['array']);
        }

        if (array_key_exists('file_system', $config)) {
            $this->_usedProperties['fileSystem'] = true;
            $this->fileSystem = new \Symfony\Config\SuluPreview\Cache\FileSystemConfig($config['file_system']);
            unset($config['file_system']);
        }

        if (array_key_exists('redis', $config)) {
            $this->_usedProperties['redis'] = true;
            $this->redis = new \Symfony\Config\SuluPreview\Cache\RedisConfig($config['redis']);
            unset($config['redis']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['type'])) {
            $output['type'] = $this->type;
        }
        if (isset($this->_usedProperties['namespace'])) {
            $output['namespace'] = $this->namespace;
        }
        if (isset($this->_usedProperties['apc'])) {
            $output['apc'] = $this->apc->toArray();
        }
        if (isset($this->_usedProperties['apcu'])) {
            $output['apcu'] = $this->apcu->toArray();
        }
        if (isset($this->_usedProperties['array'])) {
            $output['array'] = $this->array->toArray();
        }
        if (isset($this->_usedProperties['fileSystem'])) {
            $output['file_system'] = $this->fileSystem->toArray();
        }
        if (isset($this->_usedProperties['redis'])) {
            $output['redis'] = $this->redis->toArray();
        }

        return $output;
    }

}
