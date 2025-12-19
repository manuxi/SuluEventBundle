<?php

namespace Symfony\Config\Security\ProviderConfig;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class ChainConfig 
{
    private $providers;
    private $_usedProperties = [];

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed>|string $value
     *
     * @return $this
     */
    public function providers(ParamConfigurator|string|array $value): static
    {
        $this->_usedProperties['providers'] = true;
        $this->providers = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('providers', $config)) {
            $this->_usedProperties['providers'] = true;
            $this->providers = $config['providers'];
            unset($config['providers']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['providers'])) {
            $output['providers'] = $this->providers;
        }

        return $output;
    }

}
