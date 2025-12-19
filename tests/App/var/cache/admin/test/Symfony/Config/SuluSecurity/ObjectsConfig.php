<?php

namespace Symfony\Config\SuluSecurity;

require_once __DIR__.\DIRECTORY_SEPARATOR.'Objects'.\DIRECTORY_SEPARATOR.'UserConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Objects'.\DIRECTORY_SEPARATOR.'RoleConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Objects'.\DIRECTORY_SEPARATOR.'RoleSettingConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Objects'.\DIRECTORY_SEPARATOR.'AccessControlConfig.php';

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class ObjectsConfig 
{
    private $user;
    private $role;
    private $roleSetting;
    private $accessControl;
    private $_usedProperties = [];

    /**
     * @default {"model":"Sulu\\Bundle\\SecurityBundle\\Entity\\User","repository":"Sulu\\Bundle\\SecurityBundle\\Entity\\UserRepository"}
     */
    public function user(array $value = []): \Symfony\Config\SuluSecurity\Objects\UserConfig
    {
        if (null === $this->user) {
            $this->_usedProperties['user'] = true;
            $this->user = new \Symfony\Config\SuluSecurity\Objects\UserConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "user()" has already been initialized. You cannot pass values the second time you call user().');
        }

        return $this->user;
    }

    /**
     * @default {"model":"Sulu\\Bundle\\SecurityBundle\\Entity\\Role","repository":"Sulu\\Bundle\\SecurityBundle\\Entity\\RoleRepository"}
     */
    public function role(array $value = []): \Symfony\Config\SuluSecurity\Objects\RoleConfig
    {
        if (null === $this->role) {
            $this->_usedProperties['role'] = true;
            $this->role = new \Symfony\Config\SuluSecurity\Objects\RoleConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "role()" has already been initialized. You cannot pass values the second time you call role().');
        }

        return $this->role;
    }

    /**
     * @default {"model":"Sulu\\Bundle\\SecurityBundle\\Entity\\RoleSetting","repository":"Sulu\\Bundle\\SecurityBundle\\Entity\\RoleSettingRepository"}
     */
    public function roleSetting(array $value = []): \Symfony\Config\SuluSecurity\Objects\RoleSettingConfig
    {
        if (null === $this->roleSetting) {
            $this->_usedProperties['roleSetting'] = true;
            $this->roleSetting = new \Symfony\Config\SuluSecurity\Objects\RoleSettingConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "roleSetting()" has already been initialized. You cannot pass values the second time you call roleSetting().');
        }

        return $this->roleSetting;
    }

    /**
     * @default {"model":"Sulu\\Bundle\\SecurityBundle\\Entity\\AccessControl","repository":"Sulu\\Bundle\\SecurityBundle\\Entity\\AccessControlRepository"}
     */
    public function accessControl(array $value = []): \Symfony\Config\SuluSecurity\Objects\AccessControlConfig
    {
        if (null === $this->accessControl) {
            $this->_usedProperties['accessControl'] = true;
            $this->accessControl = new \Symfony\Config\SuluSecurity\Objects\AccessControlConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "accessControl()" has already been initialized. You cannot pass values the second time you call accessControl().');
        }

        return $this->accessControl;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('user', $config)) {
            $this->_usedProperties['user'] = true;
            $this->user = new \Symfony\Config\SuluSecurity\Objects\UserConfig($config['user']);
            unset($config['user']);
        }

        if (array_key_exists('role', $config)) {
            $this->_usedProperties['role'] = true;
            $this->role = new \Symfony\Config\SuluSecurity\Objects\RoleConfig($config['role']);
            unset($config['role']);
        }

        if (array_key_exists('role_setting', $config)) {
            $this->_usedProperties['roleSetting'] = true;
            $this->roleSetting = new \Symfony\Config\SuluSecurity\Objects\RoleSettingConfig($config['role_setting']);
            unset($config['role_setting']);
        }

        if (array_key_exists('access_control', $config)) {
            $this->_usedProperties['accessControl'] = true;
            $this->accessControl = new \Symfony\Config\SuluSecurity\Objects\AccessControlConfig($config['access_control']);
            unset($config['access_control']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['user'])) {
            $output['user'] = $this->user->toArray();
        }
        if (isset($this->_usedProperties['role'])) {
            $output['role'] = $this->role->toArray();
        }
        if (isset($this->_usedProperties['roleSetting'])) {
            $output['role_setting'] = $this->roleSetting->toArray();
        }
        if (isset($this->_usedProperties['accessControl'])) {
            $output['access_control'] = $this->accessControl->toArray();
        }

        return $output;
    }

}
