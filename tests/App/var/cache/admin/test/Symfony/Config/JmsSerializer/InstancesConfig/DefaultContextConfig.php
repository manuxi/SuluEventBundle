<?php

namespace Symfony\Config\JmsSerializer\InstancesConfig;

require_once __DIR__.\DIRECTORY_SEPARATOR.'DefaultContext'.\DIRECTORY_SEPARATOR.'SerializationConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'DefaultContext'.\DIRECTORY_SEPARATOR.'DeserializationConfig.php';

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class DefaultContextConfig 
{
    private $serialization;
    private $deserialization;
    private $_usedProperties = [];

    /**
     * @template TValue of string|array
     * @param TValue $value
     * @default {"attributes":[],"groups":[]}
     * @return \Symfony\Config\JmsSerializer\InstancesConfig\DefaultContext\SerializationConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\JmsSerializer\InstancesConfig\DefaultContext\SerializationConfig : static)
     */
    public function serialization(string|array $value = []): \Symfony\Config\JmsSerializer\InstancesConfig\DefaultContext\SerializationConfig|static
    {
        if (!\is_array($value)) {
            $this->_usedProperties['serialization'] = true;
            $this->serialization = $value;

            return $this;
        }

        if (!$this->serialization instanceof \Symfony\Config\JmsSerializer\InstancesConfig\DefaultContext\SerializationConfig) {
            $this->_usedProperties['serialization'] = true;
            $this->serialization = new \Symfony\Config\JmsSerializer\InstancesConfig\DefaultContext\SerializationConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "serialization()" has already been initialized. You cannot pass values the second time you call serialization().');
        }

        return $this->serialization;
    }

    /**
     * @template TValue of string|array
     * @param TValue $value
     * @default {"attributes":[],"groups":[]}
     * @return \Symfony\Config\JmsSerializer\InstancesConfig\DefaultContext\DeserializationConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\JmsSerializer\InstancesConfig\DefaultContext\DeserializationConfig : static)
     */
    public function deserialization(string|array $value = []): \Symfony\Config\JmsSerializer\InstancesConfig\DefaultContext\DeserializationConfig|static
    {
        if (!\is_array($value)) {
            $this->_usedProperties['deserialization'] = true;
            $this->deserialization = $value;

            return $this;
        }

        if (!$this->deserialization instanceof \Symfony\Config\JmsSerializer\InstancesConfig\DefaultContext\DeserializationConfig) {
            $this->_usedProperties['deserialization'] = true;
            $this->deserialization = new \Symfony\Config\JmsSerializer\InstancesConfig\DefaultContext\DeserializationConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "deserialization()" has already been initialized. You cannot pass values the second time you call deserialization().');
        }

        return $this->deserialization;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('serialization', $config)) {
            $this->_usedProperties['serialization'] = true;
            $this->serialization = \is_array($config['serialization']) ? new \Symfony\Config\JmsSerializer\InstancesConfig\DefaultContext\SerializationConfig($config['serialization']) : $config['serialization'];
            unset($config['serialization']);
        }

        if (array_key_exists('deserialization', $config)) {
            $this->_usedProperties['deserialization'] = true;
            $this->deserialization = \is_array($config['deserialization']) ? new \Symfony\Config\JmsSerializer\InstancesConfig\DefaultContext\DeserializationConfig($config['deserialization']) : $config['deserialization'];
            unset($config['deserialization']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['serialization'])) {
            $output['serialization'] = $this->serialization instanceof \Symfony\Config\JmsSerializer\InstancesConfig\DefaultContext\SerializationConfig ? $this->serialization->toArray() : $this->serialization;
        }
        if (isset($this->_usedProperties['deserialization'])) {
            $output['deserialization'] = $this->deserialization instanceof \Symfony\Config\JmsSerializer\InstancesConfig\DefaultContext\DeserializationConfig ? $this->deserialization->toArray() : $this->deserialization;
        }

        return $output;
    }

}
