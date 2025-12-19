<?php

namespace Symfony\Config\JmsSerializer\InstancesConfig\Handlers;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class ArrayCollectionConfig 
{
    private $initializeExcluded;
    private $_usedProperties = [];

    /**
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function initializeExcluded($value): static
    {
        $this->_usedProperties['initializeExcluded'] = true;
        $this->initializeExcluded = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('initialize_excluded', $config)) {
            $this->_usedProperties['initializeExcluded'] = true;
            $this->initializeExcluded = $config['initialize_excluded'];
            unset($config['initialize_excluded']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['initializeExcluded'])) {
            $output['initialize_excluded'] = $this->initializeExcluded;
        }

        return $output;
    }

}
