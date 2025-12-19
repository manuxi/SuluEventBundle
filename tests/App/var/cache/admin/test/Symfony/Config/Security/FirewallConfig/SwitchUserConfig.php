<?php

namespace Symfony\Config\Security\FirewallConfig;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class SwitchUserConfig 
{
    private $provider;
    private $parameter;
    private $role;
    private $targetRoute;
    private $_usedProperties = [];

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function provider($value): static
    {
        $this->_usedProperties['provider'] = true;
        $this->provider = $value;

        return $this;
    }

    /**
     * @default '_switch_user'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function parameter($value): static
    {
        $this->_usedProperties['parameter'] = true;
        $this->parameter = $value;

        return $this;
    }

    /**
     * @default 'ROLE_ALLOWED_TO_SWITCH'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function role($value): static
    {
        $this->_usedProperties['role'] = true;
        $this->role = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function targetRoute($value): static
    {
        $this->_usedProperties['targetRoute'] = true;
        $this->targetRoute = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('provider', $config)) {
            $this->_usedProperties['provider'] = true;
            $this->provider = $config['provider'];
            unset($config['provider']);
        }

        if (array_key_exists('parameter', $config)) {
            $this->_usedProperties['parameter'] = true;
            $this->parameter = $config['parameter'];
            unset($config['parameter']);
        }

        if (array_key_exists('role', $config)) {
            $this->_usedProperties['role'] = true;
            $this->role = $config['role'];
            unset($config['role']);
        }

        if (array_key_exists('target_route', $config)) {
            $this->_usedProperties['targetRoute'] = true;
            $this->targetRoute = $config['target_route'];
            unset($config['target_route']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['provider'])) {
            $output['provider'] = $this->provider;
        }
        if (isset($this->_usedProperties['parameter'])) {
            $output['parameter'] = $this->parameter;
        }
        if (isset($this->_usedProperties['role'])) {
            $output['role'] = $this->role;
        }
        if (isset($this->_usedProperties['targetRoute'])) {
            $output['target_route'] = $this->targetRoute;
        }

        return $output;
    }

}
