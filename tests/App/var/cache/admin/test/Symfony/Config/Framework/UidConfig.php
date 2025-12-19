<?php

namespace Symfony\Config\Framework;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class UidConfig 
{
    private $enabled;
    private $defaultUuidVersion;
    private $nameBasedUuidVersion;
    private $nameBasedUuidNamespace;
    private $timeBasedUuidVersion;
    private $timeBasedUuidNode;
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
     * @default 7
     * @param ParamConfigurator|7|6|4|1 $value
     * @return $this
     */
    public function defaultUuidVersion($value): static
    {
        $this->_usedProperties['defaultUuidVersion'] = true;
        $this->defaultUuidVersion = $value;

        return $this;
    }

    /**
     * @default 5
     * @param ParamConfigurator|5|3 $value
     * @return $this
     */
    public function nameBasedUuidVersion($value): static
    {
        $this->_usedProperties['nameBasedUuidVersion'] = true;
        $this->nameBasedUuidVersion = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function nameBasedUuidNamespace($value): static
    {
        $this->_usedProperties['nameBasedUuidNamespace'] = true;
        $this->nameBasedUuidNamespace = $value;

        return $this;
    }

    /**
     * @default 7
     * @param ParamConfigurator|7|6|1 $value
     * @return $this
     */
    public function timeBasedUuidVersion($value): static
    {
        $this->_usedProperties['timeBasedUuidVersion'] = true;
        $this->timeBasedUuidVersion = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function timeBasedUuidNode($value): static
    {
        $this->_usedProperties['timeBasedUuidNode'] = true;
        $this->timeBasedUuidNode = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('enabled', $config)) {
            $this->_usedProperties['enabled'] = true;
            $this->enabled = $config['enabled'];
            unset($config['enabled']);
        }

        if (array_key_exists('default_uuid_version', $config)) {
            $this->_usedProperties['defaultUuidVersion'] = true;
            $this->defaultUuidVersion = $config['default_uuid_version'];
            unset($config['default_uuid_version']);
        }

        if (array_key_exists('name_based_uuid_version', $config)) {
            $this->_usedProperties['nameBasedUuidVersion'] = true;
            $this->nameBasedUuidVersion = $config['name_based_uuid_version'];
            unset($config['name_based_uuid_version']);
        }

        if (array_key_exists('name_based_uuid_namespace', $config)) {
            $this->_usedProperties['nameBasedUuidNamespace'] = true;
            $this->nameBasedUuidNamespace = $config['name_based_uuid_namespace'];
            unset($config['name_based_uuid_namespace']);
        }

        if (array_key_exists('time_based_uuid_version', $config)) {
            $this->_usedProperties['timeBasedUuidVersion'] = true;
            $this->timeBasedUuidVersion = $config['time_based_uuid_version'];
            unset($config['time_based_uuid_version']);
        }

        if (array_key_exists('time_based_uuid_node', $config)) {
            $this->_usedProperties['timeBasedUuidNode'] = true;
            $this->timeBasedUuidNode = $config['time_based_uuid_node'];
            unset($config['time_based_uuid_node']);
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
        if (isset($this->_usedProperties['defaultUuidVersion'])) {
            $output['default_uuid_version'] = $this->defaultUuidVersion;
        }
        if (isset($this->_usedProperties['nameBasedUuidVersion'])) {
            $output['name_based_uuid_version'] = $this->nameBasedUuidVersion;
        }
        if (isset($this->_usedProperties['nameBasedUuidNamespace'])) {
            $output['name_based_uuid_namespace'] = $this->nameBasedUuidNamespace;
        }
        if (isset($this->_usedProperties['timeBasedUuidVersion'])) {
            $output['time_based_uuid_version'] = $this->timeBasedUuidVersion;
        }
        if (isset($this->_usedProperties['timeBasedUuidNode'])) {
            $output['time_based_uuid_node'] = $this->timeBasedUuidNode;
        }

        return $output;
    }

}
