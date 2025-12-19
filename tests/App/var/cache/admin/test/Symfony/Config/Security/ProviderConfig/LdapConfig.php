<?php

namespace Symfony\Config\Security\ProviderConfig;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class LdapConfig 
{
    private $service;
    private $baseDn;
    private $searchDn;
    private $searchPassword;
    private $extraFields;
    private $defaultRoles;
    private $roleFetcher;
    private $uidKey;
    private $filter;
    private $passwordAttribute;
    private $_usedProperties = [];

    /**
     * @example ldap
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function service($value): static
    {
        $this->_usedProperties['service'] = true;
        $this->service = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function baseDn($value): static
    {
        $this->_usedProperties['baseDn'] = true;
        $this->baseDn = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function searchDn($value): static
    {
        $this->_usedProperties['searchDn'] = true;
        $this->searchDn = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function searchPassword($value): static
    {
        $this->_usedProperties['searchPassword'] = true;
        $this->searchPassword = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed> $value
     *
     * @return $this
     */
    public function extraFields(ParamConfigurator|array $value): static
    {
        $this->_usedProperties['extraFields'] = true;
        $this->extraFields = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed>|string $value
     *
     * @return $this
     */
    public function defaultRoles(ParamConfigurator|string|array $value): static
    {
        $this->_usedProperties['defaultRoles'] = true;
        $this->defaultRoles = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function roleFetcher($value): static
    {
        $this->_usedProperties['roleFetcher'] = true;
        $this->roleFetcher = $value;

        return $this;
    }

    /**
     * @default 'sAMAccountName'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function uidKey($value): static
    {
        $this->_usedProperties['uidKey'] = true;
        $this->uidKey = $value;

        return $this;
    }

    /**
     * @default '({uid_key}={user_identifier})'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function filter($value): static
    {
        $this->_usedProperties['filter'] = true;
        $this->filter = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function passwordAttribute($value): static
    {
        $this->_usedProperties['passwordAttribute'] = true;
        $this->passwordAttribute = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('service', $config)) {
            $this->_usedProperties['service'] = true;
            $this->service = $config['service'];
            unset($config['service']);
        }

        if (array_key_exists('base_dn', $config)) {
            $this->_usedProperties['baseDn'] = true;
            $this->baseDn = $config['base_dn'];
            unset($config['base_dn']);
        }

        if (array_key_exists('search_dn', $config)) {
            $this->_usedProperties['searchDn'] = true;
            $this->searchDn = $config['search_dn'];
            unset($config['search_dn']);
        }

        if (array_key_exists('search_password', $config)) {
            $this->_usedProperties['searchPassword'] = true;
            $this->searchPassword = $config['search_password'];
            unset($config['search_password']);
        }

        if (array_key_exists('extra_fields', $config)) {
            $this->_usedProperties['extraFields'] = true;
            $this->extraFields = $config['extra_fields'];
            unset($config['extra_fields']);
        }

        if (array_key_exists('default_roles', $config)) {
            $this->_usedProperties['defaultRoles'] = true;
            $this->defaultRoles = $config['default_roles'];
            unset($config['default_roles']);
        }

        if (array_key_exists('role_fetcher', $config)) {
            $this->_usedProperties['roleFetcher'] = true;
            $this->roleFetcher = $config['role_fetcher'];
            unset($config['role_fetcher']);
        }

        if (array_key_exists('uid_key', $config)) {
            $this->_usedProperties['uidKey'] = true;
            $this->uidKey = $config['uid_key'];
            unset($config['uid_key']);
        }

        if (array_key_exists('filter', $config)) {
            $this->_usedProperties['filter'] = true;
            $this->filter = $config['filter'];
            unset($config['filter']);
        }

        if (array_key_exists('password_attribute', $config)) {
            $this->_usedProperties['passwordAttribute'] = true;
            $this->passwordAttribute = $config['password_attribute'];
            unset($config['password_attribute']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['service'])) {
            $output['service'] = $this->service;
        }
        if (isset($this->_usedProperties['baseDn'])) {
            $output['base_dn'] = $this->baseDn;
        }
        if (isset($this->_usedProperties['searchDn'])) {
            $output['search_dn'] = $this->searchDn;
        }
        if (isset($this->_usedProperties['searchPassword'])) {
            $output['search_password'] = $this->searchPassword;
        }
        if (isset($this->_usedProperties['extraFields'])) {
            $output['extra_fields'] = $this->extraFields;
        }
        if (isset($this->_usedProperties['defaultRoles'])) {
            $output['default_roles'] = $this->defaultRoles;
        }
        if (isset($this->_usedProperties['roleFetcher'])) {
            $output['role_fetcher'] = $this->roleFetcher;
        }
        if (isset($this->_usedProperties['uidKey'])) {
            $output['uid_key'] = $this->uidKey;
        }
        if (isset($this->_usedProperties['filter'])) {
            $output['filter'] = $this->filter;
        }
        if (isset($this->_usedProperties['passwordAttribute'])) {
            $output['password_attribute'] = $this->passwordAttribute;
        }

        return $output;
    }

}
