<?php

namespace Symfony\Config\Security\FirewallConfig;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class FormLoginLdapConfig 
{
    private $provider;
    private $rememberMe;
    private $successHandler;
    private $failureHandler;
    private $checkPath;
    private $useForward;
    private $loginPath;
    private $usernameParameter;
    private $passwordParameter;
    private $csrfParameter;
    private $csrfTokenId;
    private $enableCsrf;
    private $postOnly;
    private $formOnly;
    private $alwaysUseDefaultTargetPath;
    private $defaultTargetPath;
    private $targetPathParameter;
    private $useReferer;
    private $failurePath;
    private $failureForward;
    private $failurePathParameter;
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
     * @default '_username'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function usernameParameter($value): static
    {
        $this->_usedProperties['usernameParameter'] = true;
        $this->usernameParameter = $value;

        return $this;
    }

    /**
     * @default '_password'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function passwordParameter($value): static
    {
        $this->_usedProperties['passwordParameter'] = true;
        $this->passwordParameter = $value;

        return $this;
    }

    /**
     * @default '_csrf_token'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function csrfParameter($value): static
    {
        $this->_usedProperties['csrfParameter'] = true;
        $this->csrfParameter = $value;

        return $this;
    }

    /**
     * @default 'authenticate'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function csrfTokenId($value): static
    {
        $this->_usedProperties['csrfTokenId'] = true;
        $this->csrfTokenId = $value;

        return $this;
    }

    /**
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function enableCsrf($value): static
    {
        $this->_usedProperties['enableCsrf'] = true;
        $this->enableCsrf = $value;

        return $this;
    }

    /**
     * @default true
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function postOnly($value): static
    {
        $this->_usedProperties['postOnly'] = true;
        $this->postOnly = $value;

        return $this;
    }

    /**
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function formOnly($value): static
    {
        $this->_usedProperties['formOnly'] = true;
        $this->formOnly = $value;

        return $this;
    }

    /**
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function alwaysUseDefaultTargetPath($value): static
    {
        $this->_usedProperties['alwaysUseDefaultTargetPath'] = true;
        $this->alwaysUseDefaultTargetPath = $value;

        return $this;
    }

    /**
     * @default '/'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function defaultTargetPath($value): static
    {
        $this->_usedProperties['defaultTargetPath'] = true;
        $this->defaultTargetPath = $value;

        return $this;
    }

    /**
     * @default '_target_path'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function targetPathParameter($value): static
    {
        $this->_usedProperties['targetPathParameter'] = true;
        $this->targetPathParameter = $value;

        return $this;
    }

    /**
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function useReferer($value): static
    {
        $this->_usedProperties['useReferer'] = true;
        $this->useReferer = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function failurePath($value): static
    {
        $this->_usedProperties['failurePath'] = true;
        $this->failurePath = $value;

        return $this;
    }

    /**
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function failureForward($value): static
    {
        $this->_usedProperties['failureForward'] = true;
        $this->failureForward = $value;

        return $this;
    }

    /**
     * @default '_failure_path'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function failurePathParameter($value): static
    {
        $this->_usedProperties['failurePathParameter'] = true;
        $this->failurePathParameter = $value;

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

        if (array_key_exists('username_parameter', $config)) {
            $this->_usedProperties['usernameParameter'] = true;
            $this->usernameParameter = $config['username_parameter'];
            unset($config['username_parameter']);
        }

        if (array_key_exists('password_parameter', $config)) {
            $this->_usedProperties['passwordParameter'] = true;
            $this->passwordParameter = $config['password_parameter'];
            unset($config['password_parameter']);
        }

        if (array_key_exists('csrf_parameter', $config)) {
            $this->_usedProperties['csrfParameter'] = true;
            $this->csrfParameter = $config['csrf_parameter'];
            unset($config['csrf_parameter']);
        }

        if (array_key_exists('csrf_token_id', $config)) {
            $this->_usedProperties['csrfTokenId'] = true;
            $this->csrfTokenId = $config['csrf_token_id'];
            unset($config['csrf_token_id']);
        }

        if (array_key_exists('enable_csrf', $config)) {
            $this->_usedProperties['enableCsrf'] = true;
            $this->enableCsrf = $config['enable_csrf'];
            unset($config['enable_csrf']);
        }

        if (array_key_exists('post_only', $config)) {
            $this->_usedProperties['postOnly'] = true;
            $this->postOnly = $config['post_only'];
            unset($config['post_only']);
        }

        if (array_key_exists('form_only', $config)) {
            $this->_usedProperties['formOnly'] = true;
            $this->formOnly = $config['form_only'];
            unset($config['form_only']);
        }

        if (array_key_exists('always_use_default_target_path', $config)) {
            $this->_usedProperties['alwaysUseDefaultTargetPath'] = true;
            $this->alwaysUseDefaultTargetPath = $config['always_use_default_target_path'];
            unset($config['always_use_default_target_path']);
        }

        if (array_key_exists('default_target_path', $config)) {
            $this->_usedProperties['defaultTargetPath'] = true;
            $this->defaultTargetPath = $config['default_target_path'];
            unset($config['default_target_path']);
        }

        if (array_key_exists('target_path_parameter', $config)) {
            $this->_usedProperties['targetPathParameter'] = true;
            $this->targetPathParameter = $config['target_path_parameter'];
            unset($config['target_path_parameter']);
        }

        if (array_key_exists('use_referer', $config)) {
            $this->_usedProperties['useReferer'] = true;
            $this->useReferer = $config['use_referer'];
            unset($config['use_referer']);
        }

        if (array_key_exists('failure_path', $config)) {
            $this->_usedProperties['failurePath'] = true;
            $this->failurePath = $config['failure_path'];
            unset($config['failure_path']);
        }

        if (array_key_exists('failure_forward', $config)) {
            $this->_usedProperties['failureForward'] = true;
            $this->failureForward = $config['failure_forward'];
            unset($config['failure_forward']);
        }

        if (array_key_exists('failure_path_parameter', $config)) {
            $this->_usedProperties['failurePathParameter'] = true;
            $this->failurePathParameter = $config['failure_path_parameter'];
            unset($config['failure_path_parameter']);
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
        if (isset($this->_usedProperties['usernameParameter'])) {
            $output['username_parameter'] = $this->usernameParameter;
        }
        if (isset($this->_usedProperties['passwordParameter'])) {
            $output['password_parameter'] = $this->passwordParameter;
        }
        if (isset($this->_usedProperties['csrfParameter'])) {
            $output['csrf_parameter'] = $this->csrfParameter;
        }
        if (isset($this->_usedProperties['csrfTokenId'])) {
            $output['csrf_token_id'] = $this->csrfTokenId;
        }
        if (isset($this->_usedProperties['enableCsrf'])) {
            $output['enable_csrf'] = $this->enableCsrf;
        }
        if (isset($this->_usedProperties['postOnly'])) {
            $output['post_only'] = $this->postOnly;
        }
        if (isset($this->_usedProperties['formOnly'])) {
            $output['form_only'] = $this->formOnly;
        }
        if (isset($this->_usedProperties['alwaysUseDefaultTargetPath'])) {
            $output['always_use_default_target_path'] = $this->alwaysUseDefaultTargetPath;
        }
        if (isset($this->_usedProperties['defaultTargetPath'])) {
            $output['default_target_path'] = $this->defaultTargetPath;
        }
        if (isset($this->_usedProperties['targetPathParameter'])) {
            $output['target_path_parameter'] = $this->targetPathParameter;
        }
        if (isset($this->_usedProperties['useReferer'])) {
            $output['use_referer'] = $this->useReferer;
        }
        if (isset($this->_usedProperties['failurePath'])) {
            $output['failure_path'] = $this->failurePath;
        }
        if (isset($this->_usedProperties['failureForward'])) {
            $output['failure_forward'] = $this->failureForward;
        }
        if (isset($this->_usedProperties['failurePathParameter'])) {
            $output['failure_path_parameter'] = $this->failurePathParameter;
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
