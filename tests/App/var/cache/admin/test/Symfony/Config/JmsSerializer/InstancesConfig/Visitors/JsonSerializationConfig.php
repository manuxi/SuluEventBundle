<?php

namespace Symfony\Config\JmsSerializer\InstancesConfig\Visitors;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class JsonSerializationConfig 
{
    private $depth;
    private $options;
    private $_usedProperties = [];

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function depth($value): static
    {
        $this->_usedProperties['depth'] = true;
        $this->depth = $value;

        return $this;
    }

    /**
     * @default 1024
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function options($value): static
    {
        $this->_usedProperties['options'] = true;
        $this->options = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('depth', $config)) {
            $this->_usedProperties['depth'] = true;
            $this->depth = $config['depth'];
            unset($config['depth']);
        }

        if (array_key_exists('options', $config)) {
            $this->_usedProperties['options'] = true;
            $this->options = $config['options'];
            unset($config['options']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['depth'])) {
            $output['depth'] = $this->depth;
        }
        if (isset($this->_usedProperties['options'])) {
            $output['options'] = $this->options;
        }

        return $output;
    }

}
