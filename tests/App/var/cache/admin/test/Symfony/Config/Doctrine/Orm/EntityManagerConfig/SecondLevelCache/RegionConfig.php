<?php

namespace Symfony\Config\Doctrine\Orm\EntityManagerConfig\SecondLevelCache;

require_once __DIR__.\DIRECTORY_SEPARATOR.'RegionConfig'.\DIRECTORY_SEPARATOR.'CacheDriverConfig.php';

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Loader\ParamConfigurator;

/**
 * This class is automatically generated to help in creating a config.
 */
class RegionConfig 
{
    private $cacheDriver;
    private $lockPath;
    private $lockLifetime;
    private $type;
    private $lifetime;
    private $service;
    private $name;
    private $_usedProperties = [];

    /**
     * @template TValue of string|array
     * @param TValue $value
     * @default {"type":null}
     * @return \Symfony\Config\Doctrine\Orm\EntityManagerConfig\SecondLevelCache\RegionConfig\CacheDriverConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\Doctrine\Orm\EntityManagerConfig\SecondLevelCache\RegionConfig\CacheDriverConfig : static)
     */
    public function cacheDriver(string|array $value = []): \Symfony\Config\Doctrine\Orm\EntityManagerConfig\SecondLevelCache\RegionConfig\CacheDriverConfig|static
    {
        if (!\is_array($value)) {
            $this->_usedProperties['cacheDriver'] = true;
            $this->cacheDriver = $value;

            return $this;
        }

        if (!$this->cacheDriver instanceof \Symfony\Config\Doctrine\Orm\EntityManagerConfig\SecondLevelCache\RegionConfig\CacheDriverConfig) {
            $this->_usedProperties['cacheDriver'] = true;
            $this->cacheDriver = new \Symfony\Config\Doctrine\Orm\EntityManagerConfig\SecondLevelCache\RegionConfig\CacheDriverConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "cacheDriver()" has already been initialized. You cannot pass values the second time you call cacheDriver().');
        }

        return $this->cacheDriver;
    }

    /**
     * @default '%kernel.cache_dir%/doctrine/orm/slc/filelock'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function lockPath($value): static
    {
        $this->_usedProperties['lockPath'] = true;
        $this->lockPath = $value;

        return $this;
    }

    /**
     * @default 60
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function lockLifetime($value): static
    {
        $this->_usedProperties['lockLifetime'] = true;
        $this->lockLifetime = $value;

        return $this;
    }

    /**
     * @default 'default'
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
     * @default 0
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function lifetime($value): static
    {
        $this->_usedProperties['lifetime'] = true;
        $this->lifetime = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function service($value): static
    {
        $this->_usedProperties['service'] = true;
        $this->service = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function name($value): static
    {
        $this->_usedProperties['name'] = true;
        $this->name = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('cache_driver', $config)) {
            $this->_usedProperties['cacheDriver'] = true;
            $this->cacheDriver = \is_array($config['cache_driver']) ? new \Symfony\Config\Doctrine\Orm\EntityManagerConfig\SecondLevelCache\RegionConfig\CacheDriverConfig($config['cache_driver']) : $config['cache_driver'];
            unset($config['cache_driver']);
        }

        if (array_key_exists('lock_path', $config)) {
            $this->_usedProperties['lockPath'] = true;
            $this->lockPath = $config['lock_path'];
            unset($config['lock_path']);
        }

        if (array_key_exists('lock_lifetime', $config)) {
            $this->_usedProperties['lockLifetime'] = true;
            $this->lockLifetime = $config['lock_lifetime'];
            unset($config['lock_lifetime']);
        }

        if (array_key_exists('type', $config)) {
            $this->_usedProperties['type'] = true;
            $this->type = $config['type'];
            unset($config['type']);
        }

        if (array_key_exists('lifetime', $config)) {
            $this->_usedProperties['lifetime'] = true;
            $this->lifetime = $config['lifetime'];
            unset($config['lifetime']);
        }

        if (array_key_exists('service', $config)) {
            $this->_usedProperties['service'] = true;
            $this->service = $config['service'];
            unset($config['service']);
        }

        if (array_key_exists('name', $config)) {
            $this->_usedProperties['name'] = true;
            $this->name = $config['name'];
            unset($config['name']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['cacheDriver'])) {
            $output['cache_driver'] = $this->cacheDriver instanceof \Symfony\Config\Doctrine\Orm\EntityManagerConfig\SecondLevelCache\RegionConfig\CacheDriverConfig ? $this->cacheDriver->toArray() : $this->cacheDriver;
        }
        if (isset($this->_usedProperties['lockPath'])) {
            $output['lock_path'] = $this->lockPath;
        }
        if (isset($this->_usedProperties['lockLifetime'])) {
            $output['lock_lifetime'] = $this->lockLifetime;
        }
        if (isset($this->_usedProperties['type'])) {
            $output['type'] = $this->type;
        }
        if (isset($this->_usedProperties['lifetime'])) {
            $output['lifetime'] = $this->lifetime;
        }
        if (isset($this->_usedProperties['service'])) {
            $output['service'] = $this->service;
        }
        if (isset($this->_usedProperties['name'])) {
            $output['name'] = $this->name;
        }

        return $output;
    }

}
