<?php

namespace Symfony\Config\Framework\Cache;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class PoolConfig 
{
    private $adapters;
    private $tags;
    private $public;
    private $defaultLifetime;
    private $provider;
    private $earlyExpirationMessageBus;
    private $clearer;
    private $_usedProperties = [];

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed>|mixed $value
     *
     * @return $this
     */
    public function adapters(mixed $value): static
    {
        $this->_usedProperties['adapters'] = true;
        $this->adapters = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function tags($value): static
    {
        $this->_usedProperties['tags'] = true;
        $this->tags = $value;

        return $this;
    }

    /**
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function public($value): static
    {
        $this->_usedProperties['public'] = true;
        $this->public = $value;

        return $this;
    }

    /**
     * Default lifetime of the pool.
     * @example "300" for 5 minutes expressed in seconds, "PT5M" for five minutes expressed as ISO 8601 time interval, or "5 minutes" as a date expression
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function defaultLifetime($value): static
    {
        $this->_usedProperties['defaultLifetime'] = true;
        $this->defaultLifetime = $value;

        return $this;
    }

    /**
     * Overwrite the setting from the default provider for this adapter.
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
     * @example "messenger.default_bus" to send early expiration events to the default Messenger bus.
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function earlyExpirationMessageBus($value): static
    {
        $this->_usedProperties['earlyExpirationMessageBus'] = true;
        $this->earlyExpirationMessageBus = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function clearer($value): static
    {
        $this->_usedProperties['clearer'] = true;
        $this->clearer = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('adapters', $config)) {
            $this->_usedProperties['adapters'] = true;
            $this->adapters = $config['adapters'];
            unset($config['adapters']);
        }

        if (array_key_exists('tags', $config)) {
            $this->_usedProperties['tags'] = true;
            $this->tags = $config['tags'];
            unset($config['tags']);
        }

        if (array_key_exists('public', $config)) {
            $this->_usedProperties['public'] = true;
            $this->public = $config['public'];
            unset($config['public']);
        }

        if (array_key_exists('default_lifetime', $config)) {
            $this->_usedProperties['defaultLifetime'] = true;
            $this->defaultLifetime = $config['default_lifetime'];
            unset($config['default_lifetime']);
        }

        if (array_key_exists('provider', $config)) {
            $this->_usedProperties['provider'] = true;
            $this->provider = $config['provider'];
            unset($config['provider']);
        }

        if (array_key_exists('early_expiration_message_bus', $config)) {
            $this->_usedProperties['earlyExpirationMessageBus'] = true;
            $this->earlyExpirationMessageBus = $config['early_expiration_message_bus'];
            unset($config['early_expiration_message_bus']);
        }

        if (array_key_exists('clearer', $config)) {
            $this->_usedProperties['clearer'] = true;
            $this->clearer = $config['clearer'];
            unset($config['clearer']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['adapters'])) {
            $output['adapters'] = $this->adapters;
        }
        if (isset($this->_usedProperties['tags'])) {
            $output['tags'] = $this->tags;
        }
        if (isset($this->_usedProperties['public'])) {
            $output['public'] = $this->public;
        }
        if (isset($this->_usedProperties['defaultLifetime'])) {
            $output['default_lifetime'] = $this->defaultLifetime;
        }
        if (isset($this->_usedProperties['provider'])) {
            $output['provider'] = $this->provider;
        }
        if (isset($this->_usedProperties['earlyExpirationMessageBus'])) {
            $output['early_expiration_message_bus'] = $this->earlyExpirationMessageBus;
        }
        if (isset($this->_usedProperties['clearer'])) {
            $output['clearer'] = $this->clearer;
        }

        return $output;
    }

}
