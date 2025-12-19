<?php

namespace Symfony\Config\JmsSerializer\InstancesConfig;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class PropertyNamingConfig 
{
    private $id;
    private $separator;
    private $lowerCase;
    private $_usedProperties = [];

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function id($value): static
    {
        $this->_usedProperties['id'] = true;
        $this->id = $value;

        return $this;
    }

    /**
     * @default '_'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function separator($value): static
    {
        $this->_usedProperties['separator'] = true;
        $this->separator = $value;

        return $this;
    }

    /**
     * @default true
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function lowerCase($value): static
    {
        $this->_usedProperties['lowerCase'] = true;
        $this->lowerCase = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('id', $config)) {
            $this->_usedProperties['id'] = true;
            $this->id = $config['id'];
            unset($config['id']);
        }

        if (array_key_exists('separator', $config)) {
            $this->_usedProperties['separator'] = true;
            $this->separator = $config['separator'];
            unset($config['separator']);
        }

        if (array_key_exists('lower_case', $config)) {
            $this->_usedProperties['lowerCase'] = true;
            $this->lowerCase = $config['lower_case'];
            unset($config['lower_case']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['id'])) {
            $output['id'] = $this->id;
        }
        if (isset($this->_usedProperties['separator'])) {
            $output['separator'] = $this->separator;
        }
        if (isset($this->_usedProperties['lowerCase'])) {
            $output['lower_case'] = $this->lowerCase;
        }

        return $output;
    }

}
