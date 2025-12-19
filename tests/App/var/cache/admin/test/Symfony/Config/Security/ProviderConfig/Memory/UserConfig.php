<?php

namespace Symfony\Config\Security\ProviderConfig\Memory;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class UserConfig 
{
    private $password;
    private $roles;
    private $_usedProperties = [];

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function password($value): static
    {
        $this->_usedProperties['password'] = true;
        $this->password = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed>|string $value
     *
     * @return $this
     */
    public function roles(ParamConfigurator|string|array $value): static
    {
        $this->_usedProperties['roles'] = true;
        $this->roles = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('password', $config)) {
            $this->_usedProperties['password'] = true;
            $this->password = $config['password'];
            unset($config['password']);
        }

        if (array_key_exists('roles', $config)) {
            $this->_usedProperties['roles'] = true;
            $this->roles = $config['roles'];
            unset($config['roles']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['password'])) {
            $output['password'] = $this->password;
        }
        if (isset($this->_usedProperties['roles'])) {
            $output['roles'] = $this->roles;
        }

        return $output;
    }

}
