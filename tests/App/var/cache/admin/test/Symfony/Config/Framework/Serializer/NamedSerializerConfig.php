<?php

namespace Symfony\Config\Framework\Serializer;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class NamedSerializerConfig 
{
    private $nameConverter;
    private $defaultContext;
    private $includeBuiltInNormalizers;
    private $includeBuiltInEncoders;
    private $_usedProperties = [];

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function nameConverter($value): static
    {
        $this->_usedProperties['nameConverter'] = true;
        $this->nameConverter = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed> $value
     *
     * @return $this
     */
    public function defaultContext(ParamConfigurator|array $value): static
    {
        $this->_usedProperties['defaultContext'] = true;
        $this->defaultContext = $value;

        return $this;
    }

    /**
     * Whether to include the built-in normalizers
     * @default true
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function includeBuiltInNormalizers($value): static
    {
        $this->_usedProperties['includeBuiltInNormalizers'] = true;
        $this->includeBuiltInNormalizers = $value;

        return $this;
    }

    /**
     * Whether to include the built-in encoders
     * @default true
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function includeBuiltInEncoders($value): static
    {
        $this->_usedProperties['includeBuiltInEncoders'] = true;
        $this->includeBuiltInEncoders = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('name_converter', $config)) {
            $this->_usedProperties['nameConverter'] = true;
            $this->nameConverter = $config['name_converter'];
            unset($config['name_converter']);
        }

        if (array_key_exists('default_context', $config)) {
            $this->_usedProperties['defaultContext'] = true;
            $this->defaultContext = $config['default_context'];
            unset($config['default_context']);
        }

        if (array_key_exists('include_built_in_normalizers', $config)) {
            $this->_usedProperties['includeBuiltInNormalizers'] = true;
            $this->includeBuiltInNormalizers = $config['include_built_in_normalizers'];
            unset($config['include_built_in_normalizers']);
        }

        if (array_key_exists('include_built_in_encoders', $config)) {
            $this->_usedProperties['includeBuiltInEncoders'] = true;
            $this->includeBuiltInEncoders = $config['include_built_in_encoders'];
            unset($config['include_built_in_encoders']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['nameConverter'])) {
            $output['name_converter'] = $this->nameConverter;
        }
        if (isset($this->_usedProperties['defaultContext'])) {
            $output['default_context'] = $this->defaultContext;
        }
        if (isset($this->_usedProperties['includeBuiltInNormalizers'])) {
            $output['include_built_in_normalizers'] = $this->includeBuiltInNormalizers;
        }
        if (isset($this->_usedProperties['includeBuiltInEncoders'])) {
            $output['include_built_in_encoders'] = $this->includeBuiltInEncoders;
        }

        return $output;
    }

}
