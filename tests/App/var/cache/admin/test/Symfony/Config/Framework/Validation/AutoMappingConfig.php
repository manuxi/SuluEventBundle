<?php

namespace Symfony\Config\Framework\Validation;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class AutoMappingConfig 
{
    private $services;
    private $_usedProperties = [];

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed> $value
     *
     * @return $this
     */
    public function services(ParamConfigurator|array $value): static
    {
        $this->_usedProperties['services'] = true;
        $this->services = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('services', $config)) {
            $this->_usedProperties['services'] = true;
            $this->services = $config['services'];
            unset($config['services']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['services'])) {
            $output['services'] = $this->services;
        }

        return $output;
    }

}
