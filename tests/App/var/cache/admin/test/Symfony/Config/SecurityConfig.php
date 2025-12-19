<?php

namespace Symfony\Config;

require_once __DIR__.\DIRECTORY_SEPARATOR.'Security'.\DIRECTORY_SEPARATOR.'AccessDecisionManagerConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Security'.\DIRECTORY_SEPARATOR.'PasswordHasherConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Security'.\DIRECTORY_SEPARATOR.'ProviderConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Security'.\DIRECTORY_SEPARATOR.'FirewallConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Security'.\DIRECTORY_SEPARATOR.'AccessControlConfig.php';

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class SecurityConfig implements \Symfony\Component\Config\Builder\ConfigBuilderInterface
{
    private $accessDeniedUrl;
    private $sessionFixationStrategy;
    private $hideUserNotFound;
    private $exposeSecurityErrors;
    private $eraseCredentials;
    private $accessDecisionManager;
    private $passwordHashers;
    private $providers;
    private $firewalls;
    private $accessControl;
    private $roleHierarchy;
    private $_usedProperties = [];
    private $_hasDeprecatedCalls = false;

    /**
     * @example /foo/error403
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function accessDeniedUrl($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['accessDeniedUrl'] = true;
        $this->accessDeniedUrl = $value;

        return $this;
    }

    /**
     * @default 'migrate'
     * @param ParamConfigurator|'none'|'migrate'|'invalidate' $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function sessionFixationStrategy($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['sessionFixationStrategy'] = true;
        $this->sessionFixationStrategy = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|bool $value
     * @deprecated Since symfony/security-bundle 7.3: The "hide_user_not_found" option is deprecated and will be removed in 8.0. Use the "expose_security_errors" option instead.
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function hideUserNotFound($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['hideUserNotFound'] = true;
        $this->hideUserNotFound = $value;

        return $this;
    }

    /**
     * @default \Symfony\Component\Security\Http\Authentication\ExposeSecurityLevel::None
     * @param ParamConfigurator|\Symfony\Component\Security\Http\Authentication\ExposeSecurityLevel::None|\Symfony\Component\Security\Http\Authentication\ExposeSecurityLevel::AccountStatus|\Symfony\Component\Security\Http\Authentication\ExposeSecurityLevel::All $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function exposeSecurityErrors($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['exposeSecurityErrors'] = true;
        $this->exposeSecurityErrors = $value;

        return $this;
    }

    /**
     * @default true
     * @param ParamConfigurator|bool $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function eraseCredentials($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['eraseCredentials'] = true;
        $this->eraseCredentials = $value;

        return $this;
    }

    /**
     * @default {"allow_if_all_abstain":false,"allow_if_equal_granted_denied":true}
     * @deprecated since Symfony 7.4
     */
    public function accessDecisionManager(array $value = []): \Symfony\Config\Security\AccessDecisionManagerConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (null === $this->accessDecisionManager) {
            $this->_usedProperties['accessDecisionManager'] = true;
            $this->accessDecisionManager = new \Symfony\Config\Security\AccessDecisionManagerConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "accessDecisionManager()" has already been initialized. You cannot pass values the second time you call accessDecisionManager().');
        }

        return $this->accessDecisionManager;
    }

    /**
     * @template TValue of string|array
     * @param TValue $value
     * @example "auto"
     * @example {"algorithm":"auto","time_cost":8,"cost":13}
     * @return \Symfony\Config\Security\PasswordHasherConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\Security\PasswordHasherConfig : static)
     * @deprecated since Symfony 7.4
     */
    public function passwordHasher(string $class, string|array $value = []): \Symfony\Config\Security\PasswordHasherConfig|static
    {
        $this->_hasDeprecatedCalls = true;
        if (!\is_array($value)) {
            $this->_usedProperties['passwordHashers'] = true;
            $this->passwordHashers[$class] = $value;

            return $this;
        }

        if (!isset($this->passwordHashers[$class]) || !$this->passwordHashers[$class] instanceof \Symfony\Config\Security\PasswordHasherConfig) {
            $this->_usedProperties['passwordHashers'] = true;
            $this->passwordHashers[$class] = new \Symfony\Config\Security\PasswordHasherConfig($value);
        } elseif (1 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "passwordHasher()" has already been initialized. You cannot pass values the second time you call passwordHasher().');
        }

        return $this->passwordHashers[$class];
    }

    /**
     * @example {"memory":{"users":{"foo":{"password":"foo","roles":"ROLE_USER"},"bar":{"password":"bar","roles":"[ROLE_USER, ROLE_ADMIN]"}}}}
     * @example {"entity":{"class":"SecurityBundle:User","property":"username"}}
     * @deprecated since Symfony 7.4
     */
    public function provider(string $name, array $value = []): \Symfony\Config\Security\ProviderConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (!isset($this->providers[$name])) {
            $this->_usedProperties['providers'] = true;
            $this->providers[$name] = new \Symfony\Config\Security\ProviderConfig($value);
        } elseif (1 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "provider()" has already been initialized. You cannot pass values the second time you call provider().');
        }

        return $this->providers[$name];
    }

    /**
     * @deprecated since Symfony 7.4
     */
    public function firewall(string $name, array $value = []): \Symfony\Config\Security\FirewallConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (!isset($this->firewalls[$name])) {
            $this->_usedProperties['firewalls'] = true;
            $this->firewalls[$name] = new \Symfony\Config\Security\FirewallConfig($value);
        } elseif (1 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "firewall()" has already been initialized. You cannot pass values the second time you call firewall().');
        }

        return $this->firewalls[$name];
    }

    /**
     * @deprecated since Symfony 7.4
     */
    public function accessControl(array $value = []): \Symfony\Config\Security\AccessControlConfig
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['accessControl'] = true;

        return $this->accessControl[] = new \Symfony\Config\Security\AccessControlConfig($value);
    }

    /**
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function roleHierarchy(string $id, ParamConfigurator|string|array $value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['roleHierarchy'] = true;
        $this->roleHierarchy[$id] = $value;

        return $this;
    }

    public function getExtensionAlias(): string
    {
        return 'security';
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('access_denied_url', $config)) {
            $this->_usedProperties['accessDeniedUrl'] = true;
            $this->accessDeniedUrl = $config['access_denied_url'];
            unset($config['access_denied_url']);
        }

        if (array_key_exists('session_fixation_strategy', $config)) {
            $this->_usedProperties['sessionFixationStrategy'] = true;
            $this->sessionFixationStrategy = $config['session_fixation_strategy'];
            unset($config['session_fixation_strategy']);
        }

        if (array_key_exists('hide_user_not_found', $config)) {
            $this->_usedProperties['hideUserNotFound'] = true;
            $this->hideUserNotFound = $config['hide_user_not_found'];
            unset($config['hide_user_not_found']);
        }

        if (array_key_exists('expose_security_errors', $config)) {
            $this->_usedProperties['exposeSecurityErrors'] = true;
            $this->exposeSecurityErrors = $config['expose_security_errors'];
            unset($config['expose_security_errors']);
        }

        if (array_key_exists('erase_credentials', $config)) {
            $this->_usedProperties['eraseCredentials'] = true;
            $this->eraseCredentials = $config['erase_credentials'];
            unset($config['erase_credentials']);
        }

        if (array_key_exists('access_decision_manager', $config)) {
            $this->_usedProperties['accessDecisionManager'] = true;
            $this->accessDecisionManager = new \Symfony\Config\Security\AccessDecisionManagerConfig($config['access_decision_manager']);
            unset($config['access_decision_manager']);
        }

        if (array_key_exists('password_hashers', $config)) {
            $this->_usedProperties['passwordHashers'] = true;
            $this->passwordHashers = array_map(fn ($v) => \is_array($v) ? new \Symfony\Config\Security\PasswordHasherConfig($v) : $v, $config['password_hashers']);
            unset($config['password_hashers']);
        }

        if (array_key_exists('providers', $config)) {
            $this->_usedProperties['providers'] = true;
            $this->providers = array_map(fn ($v) => new \Symfony\Config\Security\ProviderConfig($v), $config['providers']);
            unset($config['providers']);
        }

        if (array_key_exists('firewalls', $config)) {
            $this->_usedProperties['firewalls'] = true;
            $this->firewalls = array_map(fn ($v) => new \Symfony\Config\Security\FirewallConfig($v), $config['firewalls']);
            unset($config['firewalls']);
        }

        if (array_key_exists('access_control', $config)) {
            $this->_usedProperties['accessControl'] = true;
            $this->accessControl = array_map(fn ($v) => new \Symfony\Config\Security\AccessControlConfig($v), $config['access_control']);
            unset($config['access_control']);
        }

        if (array_key_exists('role_hierarchy', $config)) {
            $this->_usedProperties['roleHierarchy'] = true;
            $this->roleHierarchy = $config['role_hierarchy'];
            unset($config['role_hierarchy']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['accessDeniedUrl'])) {
            $output['access_denied_url'] = $this->accessDeniedUrl;
        }
        if (isset($this->_usedProperties['sessionFixationStrategy'])) {
            $output['session_fixation_strategy'] = $this->sessionFixationStrategy;
        }
        if (isset($this->_usedProperties['hideUserNotFound'])) {
            $output['hide_user_not_found'] = $this->hideUserNotFound;
        }
        if (isset($this->_usedProperties['exposeSecurityErrors'])) {
            $output['expose_security_errors'] = $this->exposeSecurityErrors;
        }
        if (isset($this->_usedProperties['eraseCredentials'])) {
            $output['erase_credentials'] = $this->eraseCredentials;
        }
        if (isset($this->_usedProperties['accessDecisionManager'])) {
            $output['access_decision_manager'] = $this->accessDecisionManager->toArray();
        }
        if (isset($this->_usedProperties['passwordHashers'])) {
            $output['password_hashers'] = array_map(fn ($v) => $v instanceof \Symfony\Config\Security\PasswordHasherConfig ? $v->toArray() : $v, $this->passwordHashers);
        }
        if (isset($this->_usedProperties['providers'])) {
            $output['providers'] = array_map(fn ($v) => $v->toArray(), $this->providers);
        }
        if (isset($this->_usedProperties['firewalls'])) {
            $output['firewalls'] = array_map(fn ($v) => $v->toArray(), $this->firewalls);
        }
        if (isset($this->_usedProperties['accessControl'])) {
            $output['access_control'] = array_map(fn ($v) => $v->toArray(), $this->accessControl);
        }
        if (isset($this->_usedProperties['roleHierarchy'])) {
            $output['role_hierarchy'] = $this->roleHierarchy;
        }
        if ($this->_hasDeprecatedCalls) {
            trigger_deprecation('symfony/config', '7.4', 'Calling any fluent method on "%s" is deprecated; pass the configuration to the constructor instead.', $this::class);
        }

        return $output;
    }

}
