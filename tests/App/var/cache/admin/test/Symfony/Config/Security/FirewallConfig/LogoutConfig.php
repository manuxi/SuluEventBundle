<?php

namespace Symfony\Config\Security\FirewallConfig;

require_once __DIR__.\DIRECTORY_SEPARATOR.'Logout'.\DIRECTORY_SEPARATOR.'DeleteCookieConfig.php';

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class LogoutConfig 
{
    private $enableCsrf;
    private $csrfTokenId;
    private $csrfParameter;
    private $csrfTokenManager;
    private $path;
    private $target;
    private $invalidateSession;
    private $clearSiteData;
    private $deleteCookies;
    private $_usedProperties = [];

    /**
     * @default null
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
     * @default 'logout'
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
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function csrfTokenManager($value): static
    {
        $this->_usedProperties['csrfTokenManager'] = true;
        $this->csrfTokenManager = $value;

        return $this;
    }

    /**
     * @default '/logout'
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
     * @default '/'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function target($value): static
    {
        $this->_usedProperties['target'] = true;
        $this->target = $value;

        return $this;
    }

    /**
     * @default true
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function invalidateSession($value): static
    {
        $this->_usedProperties['invalidateSession'] = true;
        $this->invalidateSession = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed>|string $value
     *
     * @return $this
     */
    public function clearSiteData(ParamConfigurator|string|array $value): static
    {
        $this->_usedProperties['clearSiteData'] = true;
        $this->clearSiteData = $value;

        return $this;
    }

    public function deleteCookie(string $name, array $value = []): \Symfony\Config\Security\FirewallConfig\Logout\DeleteCookieConfig
    {
        if (!isset($this->deleteCookies[$name])) {
            $this->_usedProperties['deleteCookies'] = true;
            $this->deleteCookies[$name] = new \Symfony\Config\Security\FirewallConfig\Logout\DeleteCookieConfig($value);
        } elseif (1 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "deleteCookie()" has already been initialized. You cannot pass values the second time you call deleteCookie().');
        }

        return $this->deleteCookies[$name];
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('enable_csrf', $config)) {
            $this->_usedProperties['enableCsrf'] = true;
            $this->enableCsrf = $config['enable_csrf'];
            unset($config['enable_csrf']);
        }

        if (array_key_exists('csrf_token_id', $config)) {
            $this->_usedProperties['csrfTokenId'] = true;
            $this->csrfTokenId = $config['csrf_token_id'];
            unset($config['csrf_token_id']);
        }

        if (array_key_exists('csrf_parameter', $config)) {
            $this->_usedProperties['csrfParameter'] = true;
            $this->csrfParameter = $config['csrf_parameter'];
            unset($config['csrf_parameter']);
        }

        if (array_key_exists('csrf_token_manager', $config)) {
            $this->_usedProperties['csrfTokenManager'] = true;
            $this->csrfTokenManager = $config['csrf_token_manager'];
            unset($config['csrf_token_manager']);
        }

        if (array_key_exists('path', $config)) {
            $this->_usedProperties['path'] = true;
            $this->path = $config['path'];
            unset($config['path']);
        }

        if (array_key_exists('target', $config)) {
            $this->_usedProperties['target'] = true;
            $this->target = $config['target'];
            unset($config['target']);
        }

        if (array_key_exists('invalidate_session', $config)) {
            $this->_usedProperties['invalidateSession'] = true;
            $this->invalidateSession = $config['invalidate_session'];
            unset($config['invalidate_session']);
        }

        if (array_key_exists('clear_site_data', $config)) {
            $this->_usedProperties['clearSiteData'] = true;
            $this->clearSiteData = $config['clear_site_data'];
            unset($config['clear_site_data']);
        }

        if (array_key_exists('delete_cookies', $config)) {
            $this->_usedProperties['deleteCookies'] = true;
            $this->deleteCookies = array_map(fn ($v) => \is_array($v) ? new \Symfony\Config\Security\FirewallConfig\Logout\DeleteCookieConfig($v) : $v, $config['delete_cookies']);
            unset($config['delete_cookies']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['enableCsrf'])) {
            $output['enable_csrf'] = $this->enableCsrf;
        }
        if (isset($this->_usedProperties['csrfTokenId'])) {
            $output['csrf_token_id'] = $this->csrfTokenId;
        }
        if (isset($this->_usedProperties['csrfParameter'])) {
            $output['csrf_parameter'] = $this->csrfParameter;
        }
        if (isset($this->_usedProperties['csrfTokenManager'])) {
            $output['csrf_token_manager'] = $this->csrfTokenManager;
        }
        if (isset($this->_usedProperties['path'])) {
            $output['path'] = $this->path;
        }
        if (isset($this->_usedProperties['target'])) {
            $output['target'] = $this->target;
        }
        if (isset($this->_usedProperties['invalidateSession'])) {
            $output['invalidate_session'] = $this->invalidateSession;
        }
        if (isset($this->_usedProperties['clearSiteData'])) {
            $output['clear_site_data'] = $this->clearSiteData;
        }
        if (isset($this->_usedProperties['deleteCookies'])) {
            $output['delete_cookies'] = array_map(fn ($v) => $v instanceof \Symfony\Config\Security\FirewallConfig\Logout\DeleteCookieConfig ? $v->toArray() : $v, $this->deleteCookies);
        }

        return $output;
    }

}
