<?php

namespace Symfony\Config;

require_once __DIR__.\DIRECTORY_SEPARATOR.'SuluSecurity'.\DIRECTORY_SEPARATOR.'CheckerConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'SuluSecurity'.\DIRECTORY_SEPARATOR.'PasswordPolicyConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'SuluSecurity'.\DIRECTORY_SEPARATOR.'SingleSignOnConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'SuluSecurity'.\DIRECTORY_SEPARATOR.'TwoFactorConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'SuluSecurity'.\DIRECTORY_SEPARATOR.'ResetPasswordConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'SuluSecurity'.\DIRECTORY_SEPARATOR.'ObjectsConfig.php';

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class SuluSecurityConfig implements \Symfony\Component\Config\Builder\ConfigBuilderInterface
{
    private $system;
    private $checker;
    private $passwordPolicy;
    private $singleSignOn;
    private $twoFactor;
    private $resetPassword;
    private $objects;
    private $_usedProperties = [];
    private $_hasDeprecatedCalls = false;

    /**
     * @default 'Sulu'
     * @param ParamConfigurator|mixed $value
     * @deprecated Since sulu/sulu 2.1.0: The system option is deprecated and will be removed. Setting this option in the admin context will break the permissions registered by the bundles.
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function system($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['system'] = true;
        $this->system = $value;

        return $this;
    }

    /**
     * @template TValue of array|bool
     * @param TValue $value
     * @default {"enabled":false}
     * @return \Symfony\Config\SuluSecurity\CheckerConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\SuluSecurity\CheckerConfig : static)
     * @deprecated since Symfony 7.4
     */
    public function checker(array|bool $value = []): \Symfony\Config\SuluSecurity\CheckerConfig|static
    {
        $this->_hasDeprecatedCalls = true;
        if (!\is_array($value)) {
            $this->_usedProperties['checker'] = true;
            $this->checker = $value;

            return $this;
        }

        if (!$this->checker instanceof \Symfony\Config\SuluSecurity\CheckerConfig) {
            $this->_usedProperties['checker'] = true;
            $this->checker = new \Symfony\Config\SuluSecurity\CheckerConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "checker()" has already been initialized. You cannot pass values the second time you call checker().');
        }

        return $this->checker;
    }

    /**
     * @template TValue of array|bool
     * @param TValue $value
     * @default {"enabled":false,"pattern":".{8,}","info_translation_key":"sulu_security.password_policy_information"}
     * @return \Symfony\Config\SuluSecurity\PasswordPolicyConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\SuluSecurity\PasswordPolicyConfig : static)
     * @deprecated since Symfony 7.4
     */
    public function passwordPolicy(array|bool $value = []): \Symfony\Config\SuluSecurity\PasswordPolicyConfig|static
    {
        $this->_hasDeprecatedCalls = true;
        if (!\is_array($value)) {
            $this->_usedProperties['passwordPolicy'] = true;
            $this->passwordPolicy = $value;

            return $this;
        }

        if (!$this->passwordPolicy instanceof \Symfony\Config\SuluSecurity\PasswordPolicyConfig) {
            $this->_usedProperties['passwordPolicy'] = true;
            $this->passwordPolicy = new \Symfony\Config\SuluSecurity\PasswordPolicyConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "passwordPolicy()" has already been initialized. You cannot pass values the second time you call passwordPolicy().');
        }

        return $this->passwordPolicy;
    }

    /**
     * @deprecated since Symfony 7.4
     */
    public function singleSignOn(array $value = []): \Symfony\Config\SuluSecurity\SingleSignOnConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (null === $this->singleSignOn) {
            $this->_usedProperties['singleSignOn'] = true;
            $this->singleSignOn = new \Symfony\Config\SuluSecurity\SingleSignOnConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "singleSignOn()" has already been initialized. You cannot pass values the second time you call singleSignOn().');
        }

        return $this->singleSignOn;
    }

    /**
     * @default {"email":{"template":"@SuluSecurity\/mail_templates\/two_factor"},"force":{"enabled":false,"pattern":"(.+)"}}
     * @deprecated since Symfony 7.4
     */
    public function twoFactor(array $value = []): \Symfony\Config\SuluSecurity\TwoFactorConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (null === $this->twoFactor) {
            $this->_usedProperties['twoFactor'] = true;
            $this->twoFactor = new \Symfony\Config\SuluSecurity\TwoFactorConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "twoFactor()" has already been initialized. You cannot pass values the second time you call twoFactor().');
        }

        return $this->twoFactor;
    }

    /**
     * @default {"mail":{"token_send_limit":3,"sender":"","subject":"sulu_security.reset_mail_subject","template":"@SuluSecurity\/mail_templates\/reset_password.html.twig","translation_domain":"admin"}}
     * @deprecated since Symfony 7.4
     */
    public function resetPassword(array $value = []): \Symfony\Config\SuluSecurity\ResetPasswordConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (null === $this->resetPassword) {
            $this->_usedProperties['resetPassword'] = true;
            $this->resetPassword = new \Symfony\Config\SuluSecurity\ResetPasswordConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "resetPassword()" has already been initialized. You cannot pass values the second time you call resetPassword().');
        }

        return $this->resetPassword;
    }

    /**
     * @default {"user":{"model":"Sulu\\Bundle\\SecurityBundle\\Entity\\User","repository":"Sulu\\Bundle\\SecurityBundle\\Entity\\UserRepository"},"role":{"model":"Sulu\\Bundle\\SecurityBundle\\Entity\\Role","repository":"Sulu\\Bundle\\SecurityBundle\\Entity\\RoleRepository"},"role_setting":{"model":"Sulu\\Bundle\\SecurityBundle\\Entity\\RoleSetting","repository":"Sulu\\Bundle\\SecurityBundle\\Entity\\RoleSettingRepository"},"access_control":{"model":"Sulu\\Bundle\\SecurityBundle\\Entity\\AccessControl","repository":"Sulu\\Bundle\\SecurityBundle\\Entity\\AccessControlRepository"}}
     * @deprecated since Symfony 7.4
     */
    public function objects(array $value = []): \Symfony\Config\SuluSecurity\ObjectsConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (null === $this->objects) {
            $this->_usedProperties['objects'] = true;
            $this->objects = new \Symfony\Config\SuluSecurity\ObjectsConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "objects()" has already been initialized. You cannot pass values the second time you call objects().');
        }

        return $this->objects;
    }

    public function getExtensionAlias(): string
    {
        return 'sulu_security';
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('system', $config)) {
            $this->_usedProperties['system'] = true;
            $this->system = $config['system'];
            unset($config['system']);
        }

        if (array_key_exists('checker', $config)) {
            $this->_usedProperties['checker'] = true;
            $this->checker = \is_array($config['checker']) ? new \Symfony\Config\SuluSecurity\CheckerConfig($config['checker']) : $config['checker'];
            unset($config['checker']);
        }

        if (array_key_exists('password_policy', $config)) {
            $this->_usedProperties['passwordPolicy'] = true;
            $this->passwordPolicy = \is_array($config['password_policy']) ? new \Symfony\Config\SuluSecurity\PasswordPolicyConfig($config['password_policy']) : $config['password_policy'];
            unset($config['password_policy']);
        }

        if (array_key_exists('single_sign_on', $config)) {
            $this->_usedProperties['singleSignOn'] = true;
            $this->singleSignOn = new \Symfony\Config\SuluSecurity\SingleSignOnConfig($config['single_sign_on']);
            unset($config['single_sign_on']);
        }

        if (array_key_exists('two_factor', $config)) {
            $this->_usedProperties['twoFactor'] = true;
            $this->twoFactor = new \Symfony\Config\SuluSecurity\TwoFactorConfig($config['two_factor']);
            unset($config['two_factor']);
        }

        if (array_key_exists('reset_password', $config)) {
            $this->_usedProperties['resetPassword'] = true;
            $this->resetPassword = new \Symfony\Config\SuluSecurity\ResetPasswordConfig($config['reset_password']);
            unset($config['reset_password']);
        }

        if (array_key_exists('objects', $config)) {
            $this->_usedProperties['objects'] = true;
            $this->objects = new \Symfony\Config\SuluSecurity\ObjectsConfig($config['objects']);
            unset($config['objects']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['system'])) {
            $output['system'] = $this->system;
        }
        if (isset($this->_usedProperties['checker'])) {
            $output['checker'] = $this->checker instanceof \Symfony\Config\SuluSecurity\CheckerConfig ? $this->checker->toArray() : $this->checker;
        }
        if (isset($this->_usedProperties['passwordPolicy'])) {
            $output['password_policy'] = $this->passwordPolicy instanceof \Symfony\Config\SuluSecurity\PasswordPolicyConfig ? $this->passwordPolicy->toArray() : $this->passwordPolicy;
        }
        if (isset($this->_usedProperties['singleSignOn'])) {
            $output['single_sign_on'] = $this->singleSignOn->toArray();
        }
        if (isset($this->_usedProperties['twoFactor'])) {
            $output['two_factor'] = $this->twoFactor->toArray();
        }
        if (isset($this->_usedProperties['resetPassword'])) {
            $output['reset_password'] = $this->resetPassword->toArray();
        }
        if (isset($this->_usedProperties['objects'])) {
            $output['objects'] = $this->objects->toArray();
        }
        if ($this->_hasDeprecatedCalls) {
            trigger_deprecation('symfony/config', '7.4', 'Calling any fluent method on "%s" is deprecated; pass the configuration to the constructor instead.', $this::class);
        }

        return $output;
    }

}
