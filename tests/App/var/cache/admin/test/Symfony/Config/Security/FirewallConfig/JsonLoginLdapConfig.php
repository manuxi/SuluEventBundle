<?php

namespace Symfony\Config\Security\FirewallConfig;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class JsonLoginLdapConfig 
{
    private $provider;
    private $rememberMe;
    private $successHandler;
    private $failureHandler;
    private $checkPath;
    private $useForward;
    private $loginPath;
    private $usernamePath;
    private $passwordPath;
    private $service;
    private $dnString;
    private $queryString;
    private $searchDn;
    private $searchPassword;
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
     * @default true
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function rememberMe($value): static
    {
        $this->_usedProperties['rememberMe'] = true;
        $this->rememberMe = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function successHandler($value): static
    {
        $this->_usedProperties['successHandler'] = true;
        $this->successHandler = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function failureHandler($value): static
    {
        $this->_usedProperties['failureHandler'] = true;
        $this->failureHandler = $value;

        return $this;
    }

    /**
     * @default '/login_check'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function checkPath($value): static
    {
        $this->_usedProperties['checkPath'] = true;
        $this->checkPath = $value;

        return $this;
    }

    /**
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function useForward($value): static
    {
        $this->_usedProperties['useForward'] = true;
        $this->useForward = $value;

        return $this;
    }

    /**
     * @default '/login'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function loginPath($value): static
    {
        $this->_usedProperties['loginPath'] = true;
        $this->loginPath = $value;

        return $this;
    }

    /**
     * @default 'username'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function usernamePath($value): static
    {
        $this->_usedProperties['usernamePath'] = true;
        $this->usernamePath = $value;

        return $this;
    }

    /**
     * @default 'password'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function passwordPath($value): static
    {
        $this->_usedProperties['passwordPath'] = true;
        $this->passwordPath = $value;

        return $this;
    }

    /**
     * @default 'ldap'
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
     * @default '{user_identifier}'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function dnString($value): static
    {
        $this->_usedProperties['dnString'] = true;
        $this->dnString = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function queryString($value): static
    {
        $this->_usedProperties['queryString'] = true;
        $this->queryString = $value;

        return $this;
    }

    /**
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
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function searchPassword($value): static
    {
        $this->_usedProperties['searchPassword'] = true;
        $this->searchPassword = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('provider', $config)) {
            $this->_usedProperties['provider'] = true;
            $this->provider = $config['provider'];
            unset($config['provider']);
        }

        if (array_key_exists('remember_me', $config)) {
            $this->_usedProperties['rememberMe'] = true;
            $this->rememberMe = $config['remember_me'];
            unset($config['remember_me']);
        }

        if (array_key_exists('success_handler', $config)) {
            $this->_usedProperties['successHandler'] = true;
            $this->successHandler = $config['success_handler'];
            unset($config['success_handler']);
        }

        if (array_key_exists('failure_handler', $config)) {
            $this->_usedProperties['failureHandler'] = true;
            $this->failureHandler = $config['failure_handler'];
            unset($config['failure_handler']);
        }

        if (array_key_exists('check_path', $config)) {
            $this->_usedProperties['checkPath'] = true;
            $this->checkPath = $config['check_path'];
            unset($config['check_path']);
        }

        if (array_key_exists('use_forward', $config)) {
            $this->_usedProperties['useForward'] = true;
            $this->useForward = $config['use_forward'];
            unset($config['use_forward']);
        }

        if (array_key_exists('login_path', $config)) {
            $this->_usedProperties['loginPath'] = true;
            $this->loginPath = $config['login_path'];
            unset($config['login_path']);
        }

        if (array_key_exists('username_path', $config)) {
            $this->_usedProperties['usernamePath'] = true;
            $this->usernamePath = $config['username_path'];
            unset($config['username_path']);
        }

        if (array_key_exists('password_path', $config)) {
            $this->_usedProperties['passwordPath'] = true;
            $this->passwordPath = $config['password_path'];
            unset($config['password_path']);
        }

        if (array_key_exists('service', $config)) {
            $this->_usedProperties['service'] = true;
            $this->service = $config['service'];
            unset($config['service']);
        }

        if (array_key_exists('dn_string', $config)) {
            $this->_usedProperties['dnString'] = true;
            $this->dnString = $config['dn_string'];
            unset($config['dn_string']);
        }

        if (array_key_exists('query_string', $config)) {
            $this->_usedProperties['queryString'] = true;
            $this->queryString = $config['query_string'];
            unset($config['query_string']);
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
        if (isset($this->_usedProperties['rememberMe'])) {
            $output['remember_me'] = $this->rememberMe;
        }
        if (isset($this->_usedProperties['successHandler'])) {
            $output['success_handler'] = $this->successHandler;
        }
        if (isset($this->_usedProperties['failureHandler'])) {
            $output['failure_handler'] = $this->failureHandler;
        }
        if (isset($this->_usedProperties['checkPath'])) {
            $output['check_path'] = $this->checkPath;
        }
        if (isset($this->_usedProperties['useForward'])) {
            $output['use_forward'] = $this->useForward;
        }
        if (isset($this->_usedProperties['loginPath'])) {
            $output['login_path'] = $this->loginPath;
        }
        if (isset($this->_usedProperties['usernamePath'])) {
            $output['username_path'] = $this->usernamePath;
        }
        if (isset($this->_usedProperties['passwordPath'])) {
            $output['password_path'] = $this->passwordPath;
        }
        if (isset($this->_usedProperties['service'])) {
            $output['service'] = $this->service;
        }
        if (isset($this->_usedProperties['dnString'])) {
            $output['dn_string'] = $this->dnString;
        }
        if (isset($this->_usedProperties['queryString'])) {
            $output['query_string'] = $this->queryString;
        }
        if (isset($this->_usedProperties['searchDn'])) {
            $output['search_dn'] = $this->searchDn;
        }
        if (isset($this->_usedProperties['searchPassword'])) {
            $output['search_password'] = $this->searchPassword;
        }

        return $output;
    }

}
