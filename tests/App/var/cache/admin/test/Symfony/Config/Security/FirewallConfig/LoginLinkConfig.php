<?php

namespace Symfony\Config\Security\FirewallConfig;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class LoginLinkConfig 
{
    private $checkRoute;
    private $checkPostOnly;
    private $signatureProperties;
    private $lifetime;
    private $maxUses;
    private $usedLinkCache;
    private $successHandler;
    private $failureHandler;
    private $provider;
    private $secret;
    private $alwaysUseDefaultTargetPath;
    private $defaultTargetPath;
    private $loginPath;
    private $targetPathParameter;
    private $useReferer;
    private $failurePath;
    private $failureForward;
    private $failurePathParameter;
    private $_usedProperties = [];

    /**
     * Route that will validate the login link - e.g. "app_login_link_verify".
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function checkRoute($value): static
    {
        $this->_usedProperties['checkRoute'] = true;
        $this->checkRoute = $value;

        return $this;
    }

    /**
     * If true, only HTTP POST requests to "check_route" will be handled by the authenticator.
     * @default false
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function checkPostOnly($value): static
    {
        $this->_usedProperties['checkPostOnly'] = true;
        $this->checkPostOnly = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed> $value
     *
     * @return $this
     */
    public function signatureProperties(ParamConfigurator|array $value): static
    {
        $this->_usedProperties['signatureProperties'] = true;
        $this->signatureProperties = $value;

        return $this;
    }

    /**
     * The lifetime of the login link in seconds.
     * @default 600
     * @param ParamConfigurator|int $value
     * @return $this
     */
    public function lifetime($value): static
    {
        $this->_usedProperties['lifetime'] = true;
        $this->lifetime = $value;

        return $this;
    }

    /**
     * Max number of times a login link can be used - null means unlimited within lifetime.
     * @default null
     * @param ParamConfigurator|int $value
     * @return $this
     */
    public function maxUses($value): static
    {
        $this->_usedProperties['maxUses'] = true;
        $this->maxUses = $value;

        return $this;
    }

    /**
     * Cache service id used to expired links of max_uses is set.
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function usedLinkCache($value): static
    {
        $this->_usedProperties['usedLinkCache'] = true;
        $this->usedLinkCache = $value;

        return $this;
    }

    /**
     * A service id that implements Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface.
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
     * A service id that implements Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface.
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
     * The user provider to load users from.
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
     * @default '%kernel.secret%'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function secret($value): static
    {
        $this->_usedProperties['secret'] = true;
        $this->secret = $value;

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

    public function __construct(array $config = [])
    {
        if (array_key_exists('check_route', $config)) {
            $this->_usedProperties['checkRoute'] = true;
            $this->checkRoute = $config['check_route'];
            unset($config['check_route']);
        }

        if (array_key_exists('check_post_only', $config)) {
            $this->_usedProperties['checkPostOnly'] = true;
            $this->checkPostOnly = $config['check_post_only'];
            unset($config['check_post_only']);
        }

        if (array_key_exists('signature_properties', $config)) {
            $this->_usedProperties['signatureProperties'] = true;
            $this->signatureProperties = $config['signature_properties'];
            unset($config['signature_properties']);
        }

        if (array_key_exists('lifetime', $config)) {
            $this->_usedProperties['lifetime'] = true;
            $this->lifetime = $config['lifetime'];
            unset($config['lifetime']);
        }

        if (array_key_exists('max_uses', $config)) {
            $this->_usedProperties['maxUses'] = true;
            $this->maxUses = $config['max_uses'];
            unset($config['max_uses']);
        }

        if (array_key_exists('used_link_cache', $config)) {
            $this->_usedProperties['usedLinkCache'] = true;
            $this->usedLinkCache = $config['used_link_cache'];
            unset($config['used_link_cache']);
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

        if (array_key_exists('provider', $config)) {
            $this->_usedProperties['provider'] = true;
            $this->provider = $config['provider'];
            unset($config['provider']);
        }

        if (array_key_exists('secret', $config)) {
            $this->_usedProperties['secret'] = true;
            $this->secret = $config['secret'];
            unset($config['secret']);
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

        if (array_key_exists('login_path', $config)) {
            $this->_usedProperties['loginPath'] = true;
            $this->loginPath = $config['login_path'];
            unset($config['login_path']);
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

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['checkRoute'])) {
            $output['check_route'] = $this->checkRoute;
        }
        if (isset($this->_usedProperties['checkPostOnly'])) {
            $output['check_post_only'] = $this->checkPostOnly;
        }
        if (isset($this->_usedProperties['signatureProperties'])) {
            $output['signature_properties'] = $this->signatureProperties;
        }
        if (isset($this->_usedProperties['lifetime'])) {
            $output['lifetime'] = $this->lifetime;
        }
        if (isset($this->_usedProperties['maxUses'])) {
            $output['max_uses'] = $this->maxUses;
        }
        if (isset($this->_usedProperties['usedLinkCache'])) {
            $output['used_link_cache'] = $this->usedLinkCache;
        }
        if (isset($this->_usedProperties['successHandler'])) {
            $output['success_handler'] = $this->successHandler;
        }
        if (isset($this->_usedProperties['failureHandler'])) {
            $output['failure_handler'] = $this->failureHandler;
        }
        if (isset($this->_usedProperties['provider'])) {
            $output['provider'] = $this->provider;
        }
        if (isset($this->_usedProperties['secret'])) {
            $output['secret'] = $this->secret;
        }
        if (isset($this->_usedProperties['alwaysUseDefaultTargetPath'])) {
            $output['always_use_default_target_path'] = $this->alwaysUseDefaultTargetPath;
        }
        if (isset($this->_usedProperties['defaultTargetPath'])) {
            $output['default_target_path'] = $this->defaultTargetPath;
        }
        if (isset($this->_usedProperties['loginPath'])) {
            $output['login_path'] = $this->loginPath;
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

        return $output;
    }

}
