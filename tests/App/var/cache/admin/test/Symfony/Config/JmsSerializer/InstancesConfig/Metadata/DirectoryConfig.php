<?php

namespace Symfony\Config\JmsSerializer\InstancesConfig\Metadata;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class DirectoryConfig 
{
    private $path;
    private $namespacePrefix;
    private $_usedProperties = [];

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function path($value): static
    {
        $this->_usedProperties['path'] = true;
        $this->path = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function namespacePrefix($value): static
    {
        $this->_usedProperties['namespacePrefix'] = true;
        $this->namespacePrefix = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('path', $config)) {
            $this->_usedProperties['path'] = true;
            $this->path = $config['path'];
            unset($config['path']);
        }

        if (array_key_exists('namespace_prefix', $config)) {
            $this->_usedProperties['namespacePrefix'] = true;
            $this->namespacePrefix = $config['namespace_prefix'];
            unset($config['namespace_prefix']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['path'])) {
            $output['path'] = $this->path;
        }
        if (isset($this->_usedProperties['namespacePrefix'])) {
            $output['namespace_prefix'] = $this->namespacePrefix;
        }

        return $output;
    }

}
