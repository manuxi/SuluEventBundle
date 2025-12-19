<?php

namespace Symfony\Config\FosRest;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class SerializerConfig 
{
    private $version;
    private $groups;
    private $serializeNull;
    private $_usedProperties = [];

    /**
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
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function serializeNull($value): static
    {
        $this->_usedProperties['serializeNull'] = true;
        $this->serializeNull = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('version', $config)) {
            $this->_usedProperties['version'] = true;
            $this->version = $config['version'];
            unset($config['version']);
        }

        if (array_key_exists('groups', $config)) {
            $this->_usedProperties['groups'] = true;
            $this->groups = $config['groups'];
            unset($config['groups']);
        }

        if (array_key_exists('serialize_null', $config)) {
            $this->_usedProperties['serializeNull'] = true;
            $this->serializeNull = $config['serialize_null'];
            unset($config['serialize_null']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['version'])) {
            $output['version'] = $this->version;
        }
        if (isset($this->_usedProperties['groups'])) {
            $output['groups'] = $this->groups;
        }
        if (isset($this->_usedProperties['serializeNull'])) {
            $output['serialize_null'] = $this->serializeNull;
        }

        return $output;
    }

}
