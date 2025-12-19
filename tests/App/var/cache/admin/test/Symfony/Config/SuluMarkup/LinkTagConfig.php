<?php

namespace Symfony\Config\SuluMarkup;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class LinkTagConfig 
{
    private $providerAttribute;
    private $_usedProperties = [];

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function providerAttribute($value): static
    {
        $this->_usedProperties['providerAttribute'] = true;
        $this->providerAttribute = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('provider_attribute', $config)) {
            $this->_usedProperties['providerAttribute'] = true;
            $this->providerAttribute = $config['provider_attribute'];
            unset($config['provider_attribute']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['providerAttribute'])) {
            $output['provider_attribute'] = $this->providerAttribute;
        }

        return $output;
    }

}
