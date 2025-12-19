<?php

namespace Symfony\Config\JmsSerializer;

require_once __DIR__.\DIRECTORY_SEPARATOR.'Metadata'.\DIRECTORY_SEPARATOR.'WarmupConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Metadata'.\DIRECTORY_SEPARATOR.'FileCacheConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Metadata'.\DIRECTORY_SEPARATOR.'DirectoryConfig.php';

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Loader\ParamConfigurator;

/**
 * This class is automatically generated to help in creating a config.
 */
class MetadataConfig 
{
    private $warmup;
    private $cache;
    private $debug;
    private $fileCache;
    private $includeInterfaces;
    private $autoDetection;
    private $inferTypesFromDocBlock;
    private $inferTypesFromDoctrineMetadata;
    private $directories;
    private $_usedProperties = [];

    /**
     * @default {"paths":{"included":[],"excluded":[]}}
     */
    public function warmup(array $value = []): \Symfony\Config\JmsSerializer\Metadata\WarmupConfig
    {
        if (null === $this->warmup) {
            $this->_usedProperties['warmup'] = true;
            $this->warmup = new \Symfony\Config\JmsSerializer\Metadata\WarmupConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "warmup()" has already been initialized. You cannot pass values the second time you call warmup().');
        }

        return $this->warmup;
    }

    /**
     * @default 'file'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function cache($value): static
    {
        $this->_usedProperties['cache'] = true;
        $this->cache = $value;

        return $this;
    }

    /**
     * @default true
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function debug($value): static
    {
        $this->_usedProperties['debug'] = true;
        $this->debug = $value;

        return $this;
    }

    /**
     * @default {"dir":null}
     */
    public function fileCache(array $value = []): \Symfony\Config\JmsSerializer\Metadata\FileCacheConfig
    {
        if (null === $this->fileCache) {
            $this->_usedProperties['fileCache'] = true;
            $this->fileCache = new \Symfony\Config\JmsSerializer\Metadata\FileCacheConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "fileCache()" has already been initialized. You cannot pass values the second time you call fileCache().');
        }

        return $this->fileCache;
    }

    /**
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function includeInterfaces($value): static
    {
        $this->_usedProperties['includeInterfaces'] = true;
        $this->includeInterfaces = $value;

        return $this;
    }

    /**
     * @default true
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function autoDetection($value): static
    {
        $this->_usedProperties['autoDetection'] = true;
        $this->autoDetection = $value;

        return $this;
    }

    /**
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function inferTypesFromDocBlock($value): static
    {
        $this->_usedProperties['inferTypesFromDocBlock'] = true;
        $this->inferTypesFromDocBlock = $value;

        return $this;
    }

    /**
     * Infers type information from Doctrine metadata if no explicit type has been defined for a property.
     * @default true
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function inferTypesFromDoctrineMetadata($value): static
    {
        $this->_usedProperties['inferTypesFromDoctrineMetadata'] = true;
        $this->inferTypesFromDoctrineMetadata = $value;

        return $this;
    }

    public function directory(string $name, array $value = []): \Symfony\Config\JmsSerializer\Metadata\DirectoryConfig
    {
        if (!isset($this->directories[$name])) {
            $this->_usedProperties['directories'] = true;
            $this->directories[$name] = new \Symfony\Config\JmsSerializer\Metadata\DirectoryConfig($value);
        } elseif (1 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "directory()" has already been initialized. You cannot pass values the second time you call directory().');
        }

        return $this->directories[$name];
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('warmup', $config)) {
            $this->_usedProperties['warmup'] = true;
            $this->warmup = new \Symfony\Config\JmsSerializer\Metadata\WarmupConfig($config['warmup']);
            unset($config['warmup']);
        }

        if (array_key_exists('cache', $config)) {
            $this->_usedProperties['cache'] = true;
            $this->cache = $config['cache'];
            unset($config['cache']);
        }

        if (array_key_exists('debug', $config)) {
            $this->_usedProperties['debug'] = true;
            $this->debug = $config['debug'];
            unset($config['debug']);
        }

        if (array_key_exists('file_cache', $config)) {
            $this->_usedProperties['fileCache'] = true;
            $this->fileCache = new \Symfony\Config\JmsSerializer\Metadata\FileCacheConfig($config['file_cache']);
            unset($config['file_cache']);
        }

        if (array_key_exists('include_interfaces', $config)) {
            $this->_usedProperties['includeInterfaces'] = true;
            $this->includeInterfaces = $config['include_interfaces'];
            unset($config['include_interfaces']);
        }

        if (array_key_exists('auto_detection', $config)) {
            $this->_usedProperties['autoDetection'] = true;
            $this->autoDetection = $config['auto_detection'];
            unset($config['auto_detection']);
        }

        if (array_key_exists('infer_types_from_doc_block', $config)) {
            $this->_usedProperties['inferTypesFromDocBlock'] = true;
            $this->inferTypesFromDocBlock = $config['infer_types_from_doc_block'];
            unset($config['infer_types_from_doc_block']);
        }

        if (array_key_exists('infer_types_from_doctrine_metadata', $config)) {
            $this->_usedProperties['inferTypesFromDoctrineMetadata'] = true;
            $this->inferTypesFromDoctrineMetadata = $config['infer_types_from_doctrine_metadata'];
            unset($config['infer_types_from_doctrine_metadata']);
        }

        if (array_key_exists('directories', $config)) {
            $this->_usedProperties['directories'] = true;
            $this->directories = array_map(fn ($v) => new \Symfony\Config\JmsSerializer\Metadata\DirectoryConfig($v), $config['directories']);
            unset($config['directories']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['warmup'])) {
            $output['warmup'] = $this->warmup->toArray();
        }
        if (isset($this->_usedProperties['cache'])) {
            $output['cache'] = $this->cache;
        }
        if (isset($this->_usedProperties['debug'])) {
            $output['debug'] = $this->debug;
        }
        if (isset($this->_usedProperties['fileCache'])) {
            $output['file_cache'] = $this->fileCache->toArray();
        }
        if (isset($this->_usedProperties['includeInterfaces'])) {
            $output['include_interfaces'] = $this->includeInterfaces;
        }
        if (isset($this->_usedProperties['autoDetection'])) {
            $output['auto_detection'] = $this->autoDetection;
        }
        if (isset($this->_usedProperties['inferTypesFromDocBlock'])) {
            $output['infer_types_from_doc_block'] = $this->inferTypesFromDocBlock;
        }
        if (isset($this->_usedProperties['inferTypesFromDoctrineMetadata'])) {
            $output['infer_types_from_doctrine_metadata'] = $this->inferTypesFromDoctrineMetadata;
        }
        if (isset($this->_usedProperties['directories'])) {
            $output['directories'] = array_map(fn ($v) => $v->toArray(), $this->directories);
        }

        return $output;
    }

}
