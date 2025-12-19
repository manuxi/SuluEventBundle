<?php

namespace Symfony\Config\JmsSerializer;

require_once __DIR__.\DIRECTORY_SEPARATOR.'Visitors'.\DIRECTORY_SEPARATOR.'JsonSerializationConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Visitors'.\DIRECTORY_SEPARATOR.'JsonDeserializationConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Visitors'.\DIRECTORY_SEPARATOR.'XmlSerializationConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Visitors'.\DIRECTORY_SEPARATOR.'XmlDeserializationConfig.php';

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class VisitorsConfig 
{
    private $jsonSerialization;
    private $jsonDeserialization;
    private $xmlSerialization;
    private $xmlDeserialization;
    private $_usedProperties = [];

    /**
     * @default {"options":1024}
     */
    public function jsonSerialization(array $value = []): \Symfony\Config\JmsSerializer\Visitors\JsonSerializationConfig
    {
        if (null === $this->jsonSerialization) {
            $this->_usedProperties['jsonSerialization'] = true;
            $this->jsonSerialization = new \Symfony\Config\JmsSerializer\Visitors\JsonSerializationConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "jsonSerialization()" has already been initialized. You cannot pass values the second time you call jsonSerialization().');
        }

        return $this->jsonSerialization;
    }

    /**
     * @default {"options":0,"strict":false}
     */
    public function jsonDeserialization(array $value = []): \Symfony\Config\JmsSerializer\Visitors\JsonDeserializationConfig
    {
        if (null === $this->jsonDeserialization) {
            $this->_usedProperties['jsonDeserialization'] = true;
            $this->jsonDeserialization = new \Symfony\Config\JmsSerializer\Visitors\JsonDeserializationConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "jsonDeserialization()" has already been initialized. You cannot pass values the second time you call jsonDeserialization().');
        }

        return $this->jsonDeserialization;
    }

    /**
     * @default {"format_output":false,"default_root_ns":""}
     */
    public function xmlSerialization(array $value = []): \Symfony\Config\JmsSerializer\Visitors\XmlSerializationConfig
    {
        if (null === $this->xmlSerialization) {
            $this->_usedProperties['xmlSerialization'] = true;
            $this->xmlSerialization = new \Symfony\Config\JmsSerializer\Visitors\XmlSerializationConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "xmlSerialization()" has already been initialized. You cannot pass values the second time you call xmlSerialization().');
        }

        return $this->xmlSerialization;
    }

    /**
     * @default {"doctype_whitelist":[],"external_entities":false,"options":0}
     */
    public function xmlDeserialization(array $value = []): \Symfony\Config\JmsSerializer\Visitors\XmlDeserializationConfig
    {
        if (null === $this->xmlDeserialization) {
            $this->_usedProperties['xmlDeserialization'] = true;
            $this->xmlDeserialization = new \Symfony\Config\JmsSerializer\Visitors\XmlDeserializationConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "xmlDeserialization()" has already been initialized. You cannot pass values the second time you call xmlDeserialization().');
        }

        return $this->xmlDeserialization;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('json_serialization', $config)) {
            $this->_usedProperties['jsonSerialization'] = true;
            $this->jsonSerialization = new \Symfony\Config\JmsSerializer\Visitors\JsonSerializationConfig($config['json_serialization']);
            unset($config['json_serialization']);
        }

        if (array_key_exists('json_deserialization', $config)) {
            $this->_usedProperties['jsonDeserialization'] = true;
            $this->jsonDeserialization = new \Symfony\Config\JmsSerializer\Visitors\JsonDeserializationConfig($config['json_deserialization']);
            unset($config['json_deserialization']);
        }

        if (array_key_exists('xml_serialization', $config)) {
            $this->_usedProperties['xmlSerialization'] = true;
            $this->xmlSerialization = new \Symfony\Config\JmsSerializer\Visitors\XmlSerializationConfig($config['xml_serialization']);
            unset($config['xml_serialization']);
        }

        if (array_key_exists('xml_deserialization', $config)) {
            $this->_usedProperties['xmlDeserialization'] = true;
            $this->xmlDeserialization = new \Symfony\Config\JmsSerializer\Visitors\XmlDeserializationConfig($config['xml_deserialization']);
            unset($config['xml_deserialization']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['jsonSerialization'])) {
            $output['json_serialization'] = $this->jsonSerialization->toArray();
        }
        if (isset($this->_usedProperties['jsonDeserialization'])) {
            $output['json_deserialization'] = $this->jsonDeserialization->toArray();
        }
        if (isset($this->_usedProperties['xmlSerialization'])) {
            $output['xml_serialization'] = $this->xmlSerialization->toArray();
        }
        if (isset($this->_usedProperties['xmlDeserialization'])) {
            $output['xml_deserialization'] = $this->xmlDeserialization->toArray();
        }

        return $output;
    }

}
