<?php

namespace Symfony\Config\Framework\Messenger\TransportConfig;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class RetryStrategyConfig 
{
    private $service;
    private $maxRetries;
    private $delay;
    private $multiplier;
    private $maxDelay;
    private $jitter;
    private $_usedProperties = [];

    /**
     * Service id to override the retry strategy entirely.
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
     * @default 3
     * @param ParamConfigurator|int $value
     * @return $this
     */
    public function maxRetries($value): static
    {
        $this->_usedProperties['maxRetries'] = true;
        $this->maxRetries = $value;

        return $this;
    }

    /**
     * Time in ms to delay (or the initial value when multiplier is used).
     * @default 1000
     * @param ParamConfigurator|int $value
     * @return $this
     */
    public function delay($value): static
    {
        $this->_usedProperties['delay'] = true;
        $this->delay = $value;

        return $this;
    }

    /**
     * If greater than 1, delay will grow exponentially for each retry: this delay = (delay * (multiple ^ retries)).
     * @default 2
     * @param ParamConfigurator|float $value
     * @return $this
     */
    public function multiplier($value): static
    {
        $this->_usedProperties['multiplier'] = true;
        $this->multiplier = $value;

        return $this;
    }

    /**
     * Max time in ms that a retry should ever be delayed (0 = infinite).
     * @default 0
     * @param ParamConfigurator|int $value
     * @return $this
     */
    public function maxDelay($value): static
    {
        $this->_usedProperties['maxDelay'] = true;
        $this->maxDelay = $value;

        return $this;
    }

    /**
     * Randomness to apply to the delay (between 0 and 1).
     * @default 0.1
     * @param ParamConfigurator|float $value
     * @return $this
     */
    public function jitter($value): static
    {
        $this->_usedProperties['jitter'] = true;
        $this->jitter = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('service', $config)) {
            $this->_usedProperties['service'] = true;
            $this->service = $config['service'];
            unset($config['service']);
        }

        if (array_key_exists('max_retries', $config)) {
            $this->_usedProperties['maxRetries'] = true;
            $this->maxRetries = $config['max_retries'];
            unset($config['max_retries']);
        }

        if (array_key_exists('delay', $config)) {
            $this->_usedProperties['delay'] = true;
            $this->delay = $config['delay'];
            unset($config['delay']);
        }

        if (array_key_exists('multiplier', $config)) {
            $this->_usedProperties['multiplier'] = true;
            $this->multiplier = $config['multiplier'];
            unset($config['multiplier']);
        }

        if (array_key_exists('max_delay', $config)) {
            $this->_usedProperties['maxDelay'] = true;
            $this->maxDelay = $config['max_delay'];
            unset($config['max_delay']);
        }

        if (array_key_exists('jitter', $config)) {
            $this->_usedProperties['jitter'] = true;
            $this->jitter = $config['jitter'];
            unset($config['jitter']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['service'])) {
            $output['service'] = $this->service;
        }
        if (isset($this->_usedProperties['maxRetries'])) {
            $output['max_retries'] = $this->maxRetries;
        }
        if (isset($this->_usedProperties['delay'])) {
            $output['delay'] = $this->delay;
        }
        if (isset($this->_usedProperties['multiplier'])) {
            $output['multiplier'] = $this->multiplier;
        }
        if (isset($this->_usedProperties['maxDelay'])) {
            $output['max_delay'] = $this->maxDelay;
        }
        if (isset($this->_usedProperties['jitter'])) {
            $output['jitter'] = $this->jitter;
        }

        return $output;
    }

}
