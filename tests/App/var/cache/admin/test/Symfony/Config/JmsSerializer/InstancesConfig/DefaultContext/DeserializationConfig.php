<?php

namespace Symfony\Config\JmsSerializer\InstancesConfig\DefaultContext;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class DeserializationConfig 
{
    private $id;
    private $serializeNull;
    private $enableMaxDepthChecks;
    private $attributes;
    private $groups;
    private $version;
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
     * Flag if null values should be serialized
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function serializeNull($value): static
    {
        $this->_usedProperties['serializeNull'] = true;
        $this->serializeNull = $value;

        return $this;
    }

    /**
     * Flag to enable the max-depth exclusion strategy
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function enableMaxDepthChecks($value): static
    {
        $this->_usedProperties['enableMaxDepthChecks'] = true;
        $this->enableMaxDepthChecks = $value;

        return $this;
    }

    /**
     * @return $this
     */
    public function attributes(string $key, mixed $value): static
    {
        $this->_usedProperties['attributes'] = true;
        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed> $value
     *
     * @return $this
     */
    public function groups(ParamConfigurator|array $value): static
    {
        $this->_usedProperties['groups'] = true;
        $this->groups = $value;

        return $this;
    }

    /**
     * Application version to use in exclusion strategies
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function version($value): static
    {
        $this->_usedProperties['version'] = true;
        $this->version = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('id', $config)) {
            $this->_usedProperties['id'] = true;
            $this->id = $config['id'];
            unset($config['id']);
        }

        if (array_key_exists('serialize_null', $config)) {
            $this->_usedProperties['serializeNull'] = true;
            $this->serializeNull = $config['serialize_null'];
            unset($config['serialize_null']);
        }

        if (array_key_exists('enable_max_depth_checks', $config)) {
            $this->_usedProperties['enableMaxDepthChecks'] = true;
            $this->enableMaxDepthChecks = $config['enable_max_depth_checks'];
            unset($config['enable_max_depth_checks']);
        }

        if (array_key_exists('attributes', $config)) {
            $this->_usedProperties['attributes'] = true;
            $this->attributes = $config['attributes'];
            unset($config['attributes']);
        }

        if (array_key_exists('groups', $config)) {
            $this->_usedProperties['groups'] = true;
            $this->groups = $config['groups'];
            unset($config['groups']);
        }

        if (array_key_exists('version', $config)) {
            $this->_usedProperties['version'] = true;
            $this->version = $config['version'];
            unset($config['version']);
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
        if (isset($this->_usedProperties['serializeNull'])) {
            $output['serialize_null'] = $this->serializeNull;
        }
        if (isset($this->_usedProperties['enableMaxDepthChecks'])) {
            $output['enable_max_depth_checks'] = $this->enableMaxDepthChecks;
        }
        if (isset($this->_usedProperties['attributes'])) {
            $output['attributes'] = $this->attributes;
        }
        if (isset($this->_usedProperties['groups'])) {
            $output['groups'] = $this->groups;
        }
        if (isset($this->_usedProperties['version'])) {
            $output['version'] = $this->version;
        }

        return $output;
    }

}
