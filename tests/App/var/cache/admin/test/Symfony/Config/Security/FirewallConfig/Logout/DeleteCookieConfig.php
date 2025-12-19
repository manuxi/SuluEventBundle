<?php

namespace Symfony\Config\Security\FirewallConfig\Logout;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class DeleteCookieConfig 
{
    private $path;
    private $domain;
    private $secure;
    private $samesite;
    private $partitioned;
    private $_usedProperties = [];

    /**
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
    public function domain($value): static
    {
        $this->_usedProperties['domain'] = true;
        $this->domain = $value;

        return $this;
    }

    /**
     * @default false
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function secure($value): static
    {
        $this->_usedProperties['secure'] = true;
        $this->secure = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
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
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function partitioned($value): static
    {
        $this->_usedProperties['partitioned'] = true;
        $this->partitioned = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
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

        if (array_key_exists('samesite', $config)) {
            $this->_usedProperties['samesite'] = true;
            $this->samesite = $config['samesite'];
            unset($config['samesite']);
        }

        if (array_key_exists('partitioned', $config)) {
            $this->_usedProperties['partitioned'] = true;
            $this->partitioned = $config['partitioned'];
            unset($config['partitioned']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['path'])) {
            $output['path'] = $this->path;
        }
        if (isset($this->_usedProperties['domain'])) {
            $output['domain'] = $this->domain;
        }
        if (isset($this->_usedProperties['secure'])) {
            $output['secure'] = $this->secure;
        }
        if (isset($this->_usedProperties['samesite'])) {
            $output['samesite'] = $this->samesite;
        }
        if (isset($this->_usedProperties['partitioned'])) {
            $output['partitioned'] = $this->partitioned;
        }

        return $output;
    }

}
