<?php

namespace Symfony\Config\Framework;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class PropertyAccessConfig 
{
    private $enabled;
    private $magicCall;
    private $magicGet;
    private $magicSet;
    private $throwExceptionOnInvalidIndex;
    private $throwExceptionOnInvalidPropertyPath;
    private $_usedProperties = [];

    /**
     * @default true
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function enabled($value): static
    {
        $this->_usedProperties['enabled'] = true;
        $this->enabled = $value;

        return $this;
    }

    /**
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function magicCall($value): static
    {
        $this->_usedProperties['magicCall'] = true;
        $this->magicCall = $value;

        return $this;
    }

    /**
     * @default true
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function magicGet($value): static
    {
        $this->_usedProperties['magicGet'] = true;
        $this->magicGet = $value;

        return $this;
    }

    /**
     * @default true
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function magicSet($value): static
    {
        $this->_usedProperties['magicSet'] = true;
        $this->magicSet = $value;

        return $this;
    }

    /**
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function throwExceptionOnInvalidIndex($value): static
    {
        $this->_usedProperties['throwExceptionOnInvalidIndex'] = true;
        $this->throwExceptionOnInvalidIndex = $value;

        return $this;
    }

    /**
     * @default true
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function throwExceptionOnInvalidPropertyPath($value): static
    {
        $this->_usedProperties['throwExceptionOnInvalidPropertyPath'] = true;
        $this->throwExceptionOnInvalidPropertyPath = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('enabled', $config)) {
            $this->_usedProperties['enabled'] = true;
            $this->enabled = $config['enabled'];
            unset($config['enabled']);
        }

        if (array_key_exists('magic_call', $config)) {
            $this->_usedProperties['magicCall'] = true;
            $this->magicCall = $config['magic_call'];
            unset($config['magic_call']);
        }

        if (array_key_exists('magic_get', $config)) {
            $this->_usedProperties['magicGet'] = true;
            $this->magicGet = $config['magic_get'];
            unset($config['magic_get']);
        }

        if (array_key_exists('magic_set', $config)) {
            $this->_usedProperties['magicSet'] = true;
            $this->magicSet = $config['magic_set'];
            unset($config['magic_set']);
        }

        if (array_key_exists('throw_exception_on_invalid_index', $config)) {
            $this->_usedProperties['throwExceptionOnInvalidIndex'] = true;
            $this->throwExceptionOnInvalidIndex = $config['throw_exception_on_invalid_index'];
            unset($config['throw_exception_on_invalid_index']);
        }

        if (array_key_exists('throw_exception_on_invalid_property_path', $config)) {
            $this->_usedProperties['throwExceptionOnInvalidPropertyPath'] = true;
            $this->throwExceptionOnInvalidPropertyPath = $config['throw_exception_on_invalid_property_path'];
            unset($config['throw_exception_on_invalid_property_path']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['enabled'])) {
            $output['enabled'] = $this->enabled;
        }
        if (isset($this->_usedProperties['magicCall'])) {
            $output['magic_call'] = $this->magicCall;
        }
        if (isset($this->_usedProperties['magicGet'])) {
            $output['magic_get'] = $this->magicGet;
        }
        if (isset($this->_usedProperties['magicSet'])) {
            $output['magic_set'] = $this->magicSet;
        }
        if (isset($this->_usedProperties['throwExceptionOnInvalidIndex'])) {
            $output['throw_exception_on_invalid_index'] = $this->throwExceptionOnInvalidIndex;
        }
        if (isset($this->_usedProperties['throwExceptionOnInvalidPropertyPath'])) {
            $output['throw_exception_on_invalid_property_path'] = $this->throwExceptionOnInvalidPropertyPath;
        }

        return $output;
    }

}
