<?php

namespace Symfony\Config\Security\FirewallConfig\AccessToken;

require_once __DIR__.\DIRECTORY_SEPARATOR.'TokenHandler'.\DIRECTORY_SEPARATOR.'OidcUserInfoConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'TokenHandler'.\DIRECTORY_SEPARATOR.'OidcConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'TokenHandler'.\DIRECTORY_SEPARATOR.'CasConfig.php';

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class TokenHandlerConfig 
{
    private $id;
    private $oidcUserInfo;
    private $oidc;
    private $cas;
    private $oauth2;
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

    /**
     * @template TValue of string|array
     * @param TValue $value
     * @return \Symfony\Config\Security\FirewallConfig\AccessToken\TokenHandler\OidcUserInfoConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\Security\FirewallConfig\AccessToken\TokenHandler\OidcUserInfoConfig : static)
     */
    public function oidcUserInfo(string|array $value = []): \Symfony\Config\Security\FirewallConfig\AccessToken\TokenHandler\OidcUserInfoConfig|static
    {
        if (!\is_array($value)) {
            $this->_usedProperties['oidcUserInfo'] = true;
            $this->oidcUserInfo = $value;

            return $this;
        }

        if (!$this->oidcUserInfo instanceof \Symfony\Config\Security\FirewallConfig\AccessToken\TokenHandler\OidcUserInfoConfig) {
            $this->_usedProperties['oidcUserInfo'] = true;
            $this->oidcUserInfo = new \Symfony\Config\Security\FirewallConfig\AccessToken\TokenHandler\OidcUserInfoConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "oidcUserInfo()" has already been initialized. You cannot pass values the second time you call oidcUserInfo().');
        }

        return $this->oidcUserInfo;
    }

    public function oidc(array $value = []): \Symfony\Config\Security\FirewallConfig\AccessToken\TokenHandler\OidcConfig
    {
        if (null === $this->oidc) {
            $this->_usedProperties['oidc'] = true;
            $this->oidc = new \Symfony\Config\Security\FirewallConfig\AccessToken\TokenHandler\OidcConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "oidc()" has already been initialized. You cannot pass values the second time you call oidc().');
        }

        return $this->oidc;
    }

    public function cas(array $value = []): \Symfony\Config\Security\FirewallConfig\AccessToken\TokenHandler\CasConfig
    {
        if (null === $this->cas) {
            $this->_usedProperties['cas'] = true;
            $this->cas = new \Symfony\Config\Security\FirewallConfig\AccessToken\TokenHandler\CasConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "cas()" has already been initialized. You cannot pass values the second time you call cas().');
        }

        return $this->cas;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function oauth2($value): static
    {
        $this->_usedProperties['oauth2'] = true;
        $this->oauth2 = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('id', $config)) {
            $this->_usedProperties['id'] = true;
            $this->id = $config['id'];
            unset($config['id']);
        }

        if (array_key_exists('oidc_user_info', $config)) {
            $this->_usedProperties['oidcUserInfo'] = true;
            $this->oidcUserInfo = \is_array($config['oidc_user_info']) ? new \Symfony\Config\Security\FirewallConfig\AccessToken\TokenHandler\OidcUserInfoConfig($config['oidc_user_info']) : $config['oidc_user_info'];
            unset($config['oidc_user_info']);
        }

        if (array_key_exists('oidc', $config)) {
            $this->_usedProperties['oidc'] = true;
            $this->oidc = new \Symfony\Config\Security\FirewallConfig\AccessToken\TokenHandler\OidcConfig($config['oidc']);
            unset($config['oidc']);
        }

        if (array_key_exists('cas', $config)) {
            $this->_usedProperties['cas'] = true;
            $this->cas = new \Symfony\Config\Security\FirewallConfig\AccessToken\TokenHandler\CasConfig($config['cas']);
            unset($config['cas']);
        }

        if (array_key_exists('oauth2', $config)) {
            $this->_usedProperties['oauth2'] = true;
            $this->oauth2 = $config['oauth2'];
            unset($config['oauth2']);
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
        if (isset($this->_usedProperties['oidcUserInfo'])) {
            $output['oidc_user_info'] = $this->oidcUserInfo instanceof \Symfony\Config\Security\FirewallConfig\AccessToken\TokenHandler\OidcUserInfoConfig ? $this->oidcUserInfo->toArray() : $this->oidcUserInfo;
        }
        if (isset($this->_usedProperties['oidc'])) {
            $output['oidc'] = $this->oidc->toArray();
        }
        if (isset($this->_usedProperties['cas'])) {
            $output['cas'] = $this->cas->toArray();
        }
        if (isset($this->_usedProperties['oauth2'])) {
            $output['oauth2'] = $this->oauth2;
        }

        return $output;
    }

}
