<?php

namespace Symfony\Config\Security\FirewallConfig;

require_once __DIR__.\DIRECTORY_SEPARATOR.'RememberMe'.\DIRECTORY_SEPARATOR.'TokenProviderConfig.php';

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class RememberMeConfig 
{
    private $secret;
    private $service;
    private $userProviders;
    private $catchExceptions;
    private $signatureProperties;
    private $tokenProvider;
    private $tokenVerifier;
    private $name;
    private $lifetime;
    private $path;
    private $domain;
    private $secure;
    private $httponly;
    private $samesite;
    private $alwaysRememberMe;
    private $rememberMeParameter;
    private $_usedProperties = [];

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
     * @param ParamConfigurator|list<ParamConfigurator|mixed>|string $value
     *
     * @return $this
     */
    public function userProviders(ParamConfigurator|string|array $value): static
    {
        $this->_usedProperties['userProviders'] = true;
        $this->userProviders = $value;

        return $this;
    }

    /**
     * @default true
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function catchExceptions($value): static
    {
        $this->_usedProperties['catchExceptions'] = true;
        $this->catchExceptions = $value;

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
     * @template TValue of string|array
     * @param TValue $value
     * @return \Symfony\Config\Security\FirewallConfig\RememberMe\TokenProviderConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\Security\FirewallConfig\RememberMe\TokenProviderConfig : static)
     */
    public function tokenProvider(string|array $value = []): \Symfony\Config\Security\FirewallConfig\RememberMe\TokenProviderConfig|static
    {
        if (!\is_array($value)) {
            $this->_usedProperties['tokenProvider'] = true;
            $this->tokenProvider = $value;

            return $this;
        }

        if (!$this->tokenProvider instanceof \Symfony\Config\Security\FirewallConfig\RememberMe\TokenProviderConfig) {
            $this->_usedProperties['tokenProvider'] = true;
            $this->tokenProvider = new \Symfony\Config\Security\FirewallConfig\RememberMe\TokenProviderConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "tokenProvider()" has already been initialized. You cannot pass values the second time you call tokenProvider().');
        }

        return $this->tokenProvider;
    }

    /**
     * The service ID of a custom rememberme token verifier.
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function tokenVerifier($value): static
    {
        $this->_usedProperties['tokenVerifier'] = true;
        $this->tokenVerifier = $value;

        return $this;
    }

    /**
     * @default 'REMEMBERME'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function name($value): static
    {
        $this->_usedProperties['name'] = true;
        $this->name = $value;

        return $this;
    }

    /**
     * @default 31536000
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
     * @default '/'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function path($value): static
    {
        $this->_usedProperties['path'] = true;
        $this->path = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function domain($value): static
    {
        $this->_usedProperties['domain'] = true;
        $this->domain = $value;

        return $this;
    }

    /**
     * @default false
     * @param ParamConfigurator|true|false|'auto' $value
     * @return $this
     */
    public function secure($value): static
    {
        $this->_usedProperties['secure'] = true;
        $this->secure = $value;

        return $this;
    }

    /**
     * @default true
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function httponly($value): static
    {
        $this->_usedProperties['httponly'] = true;
        $this->httponly = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|NULL|'lax'|'strict'|'none' $value
     * @return $this
     */
    public function samesite($value): static
    {
        $this->_usedProperties['samesite'] = true;
        $this->samesite = $value;

        return $this;
    }

    /**
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function alwaysRememberMe($value): static
    {
        $this->_usedProperties['alwaysRememberMe'] = true;
        $this->alwaysRememberMe = $value;

        return $this;
    }

    /**
     * @default '_remember_me'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function rememberMeParameter($value): static
    {
        $this->_usedProperties['rememberMeParameter'] = true;
        $this->rememberMeParameter = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('secret', $config)) {
            $this->_usedProperties['secret'] = true;
            $this->secret = $config['secret'];
            unset($config['secret']);
        }

        if (array_key_exists('service', $config)) {
            $this->_usedProperties['service'] = true;
            $this->service = $config['service'];
            unset($config['service']);
        }

        if (array_key_exists('user_providers', $config)) {
            $this->_usedProperties['userProviders'] = true;
            $this->userProviders = $config['user_providers'];
            unset($config['user_providers']);
        }

        if (array_key_exists('catch_exceptions', $config)) {
            $this->_usedProperties['catchExceptions'] = true;
            $this->catchExceptions = $config['catch_exceptions'];
            unset($config['catch_exceptions']);
        }

        if (array_key_exists('signature_properties', $config)) {
            $this->_usedProperties['signatureProperties'] = true;
            $this->signatureProperties = $config['signature_properties'];
            unset($config['signature_properties']);
        }

        if (array_key_exists('token_provider', $config)) {
            $this->_usedProperties['tokenProvider'] = true;
            $this->tokenProvider = \is_array($config['token_provider']) ? new \Symfony\Config\Security\FirewallConfig\RememberMe\TokenProviderConfig($config['token_provider']) : $config['token_provider'];
            unset($config['token_provider']);
        }

        if (array_key_exists('token_verifier', $config)) {
            $this->_usedProperties['tokenVerifier'] = true;
            $this->tokenVerifier = $config['token_verifier'];
            unset($config['token_verifier']);
        }

        if (array_key_exists('name', $config)) {
            $this->_usedProperties['name'] = true;
            $this->name = $config['name'];
            unset($config['name']);
        }

        if (array_key_exists('lifetime', $config)) {
            $this->_usedProperties['lifetime'] = true;
            $this->lifetime = $config['lifetime'];
            unset($config['lifetime']);
        }

        if (array_key_exists('path', $config)) {
            $this->_usedProperties['path'] = true;
            $this->path = $config['path'];
            unset($config['path']);
        }

        if (array_key_exists('domain', $config)) {
            $this->_usedProperties['domain'] = true;
            $this->domain = $config['domain'];
            unset($config['domain']);
        }

        if (array_key_exists('secure', $config)) {
            $this->_usedProperties['secure'] = true;
            $this->secure = $config['secure'];
            unset($config['secure']);
        }

        if (array_key_exists('httponly', $config)) {
            $this->_usedProperties['httponly'] = true;
            $this->httponly = $config['httponly'];
            unset($config['httponly']);
        }

        if (array_key_exists('samesite', $config)) {
            $this->_usedProperties['samesite'] = true;
            $this->samesite = $config['samesite'];
            unset($config['samesite']);
        }

        if (array_key_exists('always_remember_me', $config)) {
            $this->_usedProperties['alwaysRememberMe'] = true;
            $this->alwaysRememberMe = $config['always_remember_me'];
            unset($config['always_remember_me']);
        }

        if (array_key_exists('remember_me_parameter', $config)) {
            $this->_usedProperties['rememberMeParameter'] = true;
            $this->rememberMeParameter = $config['remember_me_parameter'];
            unset($config['remember_me_parameter']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['secret'])) {
            $output['secret'] = $this->secret;
        }
        if (isset($this->_usedProperties['service'])) {
            $output['service'] = $this->service;
        }
        if (isset($this->_usedProperties['userProviders'])) {
            $output['user_providers'] = $this->userProviders;
        }
        if (isset($this->_usedProperties['catchExceptions'])) {
            $output['catch_exceptions'] = $this->catchExceptions;
        }
        if (isset($this->_usedProperties['signatureProperties'])) {
            $output['signature_properties'] = $this->signatureProperties;
        }
        if (isset($this->_usedProperties['tokenProvider'])) {
            $output['token_provider'] = $this->tokenProvider instanceof \Symfony\Config\Security\FirewallConfig\RememberMe\TokenProviderConfig ? $this->tokenProvider->toArray() : $this->tokenProvider;
        }
        if (isset($this->_usedProperties['tokenVerifier'])) {
            $output['token_verifier'] = $this->tokenVerifier;
        }
        if (isset($this->_usedProperties['name'])) {
            $output['name'] = $this->name;
        }
        if (isset($this->_usedProperties['lifetime'])) {
            $output['lifetime'] = $this->lifetime;
        }
        if (isset($this->_usedProperties['path'])) {
            $output['path'] = $this->path;
        }
        if (isset($this->_usedProperties['domain'])) {
            $output['domain'] = $this->domain;
        }
        if (isset($this->_usedProperties['secure'])) {
            $output['secure'] = $this->secure;
        }
        if (isset($this->_usedProperties['httponly'])) {
            $output['httponly'] = $this->httponly;
        }
        if (isset($this->_usedProperties['samesite'])) {
            $output['samesite'] = $this->samesite;
        }
        if (isset($this->_usedProperties['alwaysRememberMe'])) {
            $output['always_remember_me'] = $this->alwaysRememberMe;
        }
        if (isset($this->_usedProperties['rememberMeParameter'])) {
            $output['remember_me_parameter'] = $this->rememberMeParameter;
        }

        return $output;
    }

}
