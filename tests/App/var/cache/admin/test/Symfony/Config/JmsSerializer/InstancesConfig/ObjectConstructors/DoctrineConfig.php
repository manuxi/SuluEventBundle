<?php

namespace Symfony\Config\JmsSerializer\InstancesConfig\ObjectConstructors;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class DoctrineConfig 
{
    private $enabled;
    private $fallbackStrategy;
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
     * @default 'null'
     * @param ParamConfigurator|'null'|'exception'|'fallback' $value
     * @return $this
     */
    public function fallbackStrategy($value): static
    {
        $this->_usedProperties['fallbackStrategy'] = true;
        $this->fallbackStrategy = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('enabled', $config)) {
            $this->_usedProperties['enabled'] = true;
            $this->enabled = $config['enabled'];
            unset($config['enabled']);
        }

        if (array_key_exists('fallback_strategy', $config)) {
            $this->_usedProperties['fallbackStrategy'] = true;
            $this->fallbackStrategy = $config['fallback_strategy'];
            unset($config['fallback_strategy']);
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
        if (isset($this->_usedProperties['fallbackStrategy'])) {
            $output['fallback_strategy'] = $this->fallbackStrategy;
        }

        return $output;
    }

}
