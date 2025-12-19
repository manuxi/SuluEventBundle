<?php

namespace Symfony\Config\JmsSerializer\Visitors;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class XmlDeserializationConfig 
{
    private $doctypeWhitelist;
    private $externalEntities;
    private $options;
    private $_usedProperties = [];

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed> $value
     *
     * @return $this
     */
    public function doctypeWhitelist(ParamConfigurator|array $value): static
    {
        $this->_usedProperties['doctypeWhitelist'] = true;
        $this->doctypeWhitelist = $value;

        return $this;
    }

    /**
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function externalEntities($value): static
    {
        $this->_usedProperties['externalEntities'] = true;
        $this->externalEntities = $value;

        return $this;
    }

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

    public function __construct(array $config = [])
    {
        if (array_key_exists('doctype_whitelist', $config)) {
            $this->_usedProperties['doctypeWhitelist'] = true;
            $this->doctypeWhitelist = $config['doctype_whitelist'];
            unset($config['doctype_whitelist']);
        }

        if (array_key_exists('external_entities', $config)) {
            $this->_usedProperties['externalEntities'] = true;
            $this->externalEntities = $config['external_entities'];
            unset($config['external_entities']);
        }

        if (array_key_exists('options', $config)) {
            $this->_usedProperties['options'] = true;
            $this->options = $config['options'];
            unset($config['options']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['doctypeWhitelist'])) {
            $output['doctype_whitelist'] = $this->doctypeWhitelist;
        }
        if (isset($this->_usedProperties['externalEntities'])) {
            $output['external_entities'] = $this->externalEntities;
        }
        if (isset($this->_usedProperties['options'])) {
            $output['options'] = $this->options;
        }

        return $output;
    }

}
