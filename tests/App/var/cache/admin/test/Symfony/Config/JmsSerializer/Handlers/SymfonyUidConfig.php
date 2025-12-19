<?php

namespace Symfony\Config\JmsSerializer\Handlers;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class SymfonyUidConfig 
{
    private $defaultFormat;
    private $cdata;
    private $_usedProperties = [];

    /**
     * @default 'canonical'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function defaultFormat($value): static
    {
        $this->_usedProperties['defaultFormat'] = true;
        $this->defaultFormat = $value;

        return $this;
    }

    /**
     * @default true
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function cdata($value): static
    {
        $this->_usedProperties['cdata'] = true;
        $this->cdata = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('default_format', $config)) {
            $this->_usedProperties['defaultFormat'] = true;
            $this->defaultFormat = $config['default_format'];
            unset($config['default_format']);
        }

        if (array_key_exists('cdata', $config)) {
            $this->_usedProperties['cdata'] = true;
            $this->cdata = $config['cdata'];
            unset($config['cdata']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['defaultFormat'])) {
            $output['default_format'] = $this->defaultFormat;
        }
        if (isset($this->_usedProperties['cdata'])) {
            $output['cdata'] = $this->cdata;
        }

        return $output;
    }

}
