<?php

namespace Symfony\Config\Security;

require_once __DIR__.\DIRECTORY_SEPARATOR.'ProviderConfig'.\DIRECTORY_SEPARATOR.'ChainConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'ProviderConfig'.\DIRECTORY_SEPARATOR.'EntityConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'ProviderConfig'.\DIRECTORY_SEPARATOR.'MemoryConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'ProviderConfig'.\DIRECTORY_SEPARATOR.'LdapConfig.php';

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class ProviderConfig 
{
    private $id;
    private $chain;
    private $entity;
    private $memory;
    private $ldap;
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

    public function chain(array $value = []): \Symfony\Config\Security\ProviderConfig\ChainConfig
    {
        if (null === $this->chain) {
            $this->_usedProperties['chain'] = true;
            $this->chain = new \Symfony\Config\Security\ProviderConfig\ChainConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "chain()" has already been initialized. You cannot pass values the second time you call chain().');
        }

        return $this->chain;
    }

    public function entity(array $value = []): \Symfony\Config\Security\ProviderConfig\EntityConfig
    {
        if (null === $this->entity) {
            $this->_usedProperties['entity'] = true;
            $this->entity = new \Symfony\Config\Security\ProviderConfig\EntityConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "entity()" has already been initialized. You cannot pass values the second time you call entity().');
        }

        return $this->entity;
    }

    public function memory(array $value = []): \Symfony\Config\Security\ProviderConfig\MemoryConfig
    {
        if (null === $this->memory) {
            $this->_usedProperties['memory'] = true;
            $this->memory = new \Symfony\Config\Security\ProviderConfig\MemoryConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "memory()" has already been initialized. You cannot pass values the second time you call memory().');
        }

        return $this->memory;
    }

    public function ldap(array $value = []): \Symfony\Config\Security\ProviderConfig\LdapConfig
    {
        if (null === $this->ldap) {
            $this->_usedProperties['ldap'] = true;
            $this->ldap = new \Symfony\Config\Security\ProviderConfig\LdapConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "ldap()" has already been initialized. You cannot pass values the second time you call ldap().');
        }

        return $this->ldap;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('id', $config)) {
            $this->_usedProperties['id'] = true;
            $this->id = $config['id'];
            unset($config['id']);
        }

        if (array_key_exists('chain', $config)) {
            $this->_usedProperties['chain'] = true;
            $this->chain = new \Symfony\Config\Security\ProviderConfig\ChainConfig($config['chain']);
            unset($config['chain']);
        }

        if (array_key_exists('entity', $config)) {
            $this->_usedProperties['entity'] = true;
            $this->entity = new \Symfony\Config\Security\ProviderConfig\EntityConfig($config['entity']);
            unset($config['entity']);
        }

        if (array_key_exists('memory', $config)) {
            $this->_usedProperties['memory'] = true;
            $this->memory = new \Symfony\Config\Security\ProviderConfig\MemoryConfig($config['memory']);
            unset($config['memory']);
        }

        if (array_key_exists('ldap', $config)) {
            $this->_usedProperties['ldap'] = true;
            $this->ldap = new \Symfony\Config\Security\ProviderConfig\LdapConfig($config['ldap']);
            unset($config['ldap']);
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
        if (isset($this->_usedProperties['chain'])) {
            $output['chain'] = $this->chain->toArray();
        }
        if (isset($this->_usedProperties['entity'])) {
            $output['entity'] = $this->entity->toArray();
        }
        if (isset($this->_usedProperties['memory'])) {
            $output['memory'] = $this->memory->toArray();
        }
        if (isset($this->_usedProperties['ldap'])) {
            $output['ldap'] = $this->ldap->toArray();
        }

        return $output;
    }

}
