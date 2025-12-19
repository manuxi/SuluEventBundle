<?php

namespace Symfony\Config\JmsSerializer\InstancesConfig\Visitors;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class JsonDeserializationConfig 
{
    private $options;
    private $strict;
    private $_usedProperties = [];

    /**
     * @default 0
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function options($value): static
    {
        $this->_usedProperties['options'] = true;
        $this->options = $value;

        return $this;
    }

    /**
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function strict($value): static
    {
        $this->_usedProperties['strict'] = true;
        $this->strict = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('options', $config)) {
            $this->_usedProperties['options'] = true;
            $this->options = $config['options'];
            unset($config['options']);
        }

        if (array_key_exists('strict', $config)) {
            $this->_usedProperties['strict'] = true;
            $this->strict = $config['strict'];
            unset($config['strict']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['options'])) {
            $output['options'] = $this->options;
        }
        if (isset($this->_usedProperties['strict'])) {
            $output['strict'] = $this->strict;
        }

        return $output;
    }

}
