<?php

namespace Symfony\Config\Security;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class AccessControlConfig 
{
    private $requestMatcher;
    private $requiresChannel;
    private $path;
    private $host;
    private $port;
    private $ips;
    private $attributes;
    private $route;
    private $methods;
    private $allowIf;
    private $roles;
    private $_usedProperties = [];

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function requestMatcher($value): static
    {
        $this->_usedProperties['requestMatcher'] = true;
        $this->requestMatcher = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function requiresChannel($value): static
    {
        $this->_usedProperties['requiresChannel'] = true;
        $this->requiresChannel = $value;

        return $this;
    }

    /**
     * Use the urldecoded format.
     * @example ^/path to resource/
     * @default null
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
    public function host($value): static
    {
        $this->_usedProperties['host'] = true;
        $this->host = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|int $value
     * @return $this
     */
    public function port($value): static
    {
        $this->_usedProperties['port'] = true;
        $this->port = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed>|string $value
     *
     * @return $this
     */
    public function ips(ParamConfigurator|string|array $value): static
    {
        $this->_usedProperties['ips'] = true;
        $this->ips = $value;

        return $this;
    }

    /**
     * @return $this
     */
    public function attribute(string $key, mixed $value): static
    {
        $this->_usedProperties['attributes'] = true;
        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function route($value): static
    {
        $this->_usedProperties['route'] = true;
        $this->route = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed>|string $value
     *
     * @return $this
     */
    public function methods(ParamConfigurator|string|array $value): static
    {
        $this->_usedProperties['methods'] = true;
        $this->methods = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function allowIf($value): static
    {
        $this->_usedProperties['allowIf'] = true;
        $this->allowIf = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed>|string $value
     *
     * @return $this
     */
    public function roles(ParamConfigurator|string|array $value): static
    {
        $this->_usedProperties['roles'] = true;
        $this->roles = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('request_matcher', $config)) {
            $this->_usedProperties['requestMatcher'] = true;
            $this->requestMatcher = $config['request_matcher'];
            unset($config['request_matcher']);
        }

        if (array_key_exists('requires_channel', $config)) {
            $this->_usedProperties['requiresChannel'] = true;
            $this->requiresChannel = $config['requires_channel'];
            unset($config['requires_channel']);
        }

        if (array_key_exists('path', $config)) {
            $this->_usedProperties['path'] = true;
            $this->path = $config['path'];
            unset($config['path']);
        }

        if (array_key_exists('host', $config)) {
            $this->_usedProperties['host'] = true;
            $this->host = $config['host'];
            unset($config['host']);
        }

        if (array_key_exists('port', $config)) {
            $this->_usedProperties['port'] = true;
            $this->port = $config['port'];
            unset($config['port']);
        }

        if (array_key_exists('ips', $config)) {
            $this->_usedProperties['ips'] = true;
            $this->ips = $config['ips'];
            unset($config['ips']);
        }

        if (array_key_exists('attributes', $config)) {
            $this->_usedProperties['attributes'] = true;
            $this->attributes = $config['attributes'];
            unset($config['attributes']);
        }

        if (array_key_exists('route', $config)) {
            $this->_usedProperties['route'] = true;
            $this->route = $config['route'];
            unset($config['route']);
        }

        if (array_key_exists('methods', $config)) {
            $this->_usedProperties['methods'] = true;
            $this->methods = $config['methods'];
            unset($config['methods']);
        }

        if (array_key_exists('allow_if', $config)) {
            $this->_usedProperties['allowIf'] = true;
            $this->allowIf = $config['allow_if'];
            unset($config['allow_if']);
        }

        if (array_key_exists('roles', $config)) {
            $this->_usedProperties['roles'] = true;
            $this->roles = $config['roles'];
            unset($config['roles']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['requestMatcher'])) {
            $output['request_matcher'] = $this->requestMatcher;
        }
        if (isset($this->_usedProperties['requiresChannel'])) {
            $output['requires_channel'] = $this->requiresChannel;
        }
        if (isset($this->_usedProperties['path'])) {
            $output['path'] = $this->path;
        }
        if (isset($this->_usedProperties['host'])) {
            $output['host'] = $this->host;
        }
        if (isset($this->_usedProperties['port'])) {
            $output['port'] = $this->port;
        }
        if (isset($this->_usedProperties['ips'])) {
            $output['ips'] = $this->ips;
        }
        if (isset($this->_usedProperties['attributes'])) {
            $output['attributes'] = $this->attributes;
        }
        if (isset($this->_usedProperties['route'])) {
            $output['route'] = $this->route;
        }
        if (isset($this->_usedProperties['methods'])) {
            $output['methods'] = $this->methods;
        }
        if (isset($this->_usedProperties['allowIf'])) {
            $output['allow_if'] = $this->allowIf;
        }
        if (isset($this->_usedProperties['roles'])) {
            $output['roles'] = $this->roles;
        }

        return $output;
    }

}
