<?php

namespace Symfony\Config\Framework\HttpClient\ScopedClientConfig;

require_once __DIR__.\DIRECTORY_SEPARATOR.'RetryFailed'.\DIRECTORY_SEPARATOR.'HttpCodeConfig.php';

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class RetryFailedConfig 
{
    private $enabled;
    private $retryStrategy;
    private $httpCodes;
    private $maxRetries;
    private $delay;
    private $multiplier;
    private $maxDelay;
    private $jitter;
    private $_usedProperties = [];

    /**
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function enabled($value): static
    {
        $this->_usedProperties['enabled'] = true;
        $this->enabled = $value;

        return $this;
    }

    /**
     * service id to override the retry strategy.
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function retryStrategy($value): static
    {
        $this->_usedProperties['retryStrategy'] = true;
        $this->retryStrategy = $value;

        return $this;
    }

    /**
     * A list of HTTP status code that triggers a retry.
     */
    public function httpCode(string $code, array $value = []): \Symfony\Config\Framework\HttpClient\ScopedClientConfig\RetryFailed\HttpCodeConfig
    {
        if (!isset($this->httpCodes[$code])) {
            $this->_usedProperties['httpCodes'] = true;
            $this->httpCodes[$code] = new \Symfony\Config\Framework\HttpClient\ScopedClientConfig\RetryFailed\HttpCodeConfig($value);
        } elseif (1 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "httpCode()" has already been initialized. You cannot pass values the second time you call httpCode().');
        }

        return $this->httpCodes[$code];
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
     * If greater than 1, delay will grow exponentially for each retry: delay * (multiple ^ retries).
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
     * Randomness in percent (between 0 and 1) to apply to the delay.
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
        if (array_key_exists('enabled', $config)) {
            $this->_usedProperties['enabled'] = true;
            $this->enabled = $config['enabled'];
            unset($config['enabled']);
        }

        if (array_key_exists('retry_strategy', $config)) {
            $this->_usedProperties['retryStrategy'] = true;
            $this->retryStrategy = $config['retry_strategy'];
            unset($config['retry_strategy']);
        }

        if (array_key_exists('http_codes', $config)) {
            $this->_usedProperties['httpCodes'] = true;
            $this->httpCodes = array_map(fn ($v) => new \Symfony\Config\Framework\HttpClient\ScopedClientConfig\RetryFailed\HttpCodeConfig($v), $config['http_codes']);
            unset($config['http_codes']);
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
        if (isset($this->_usedProperties['enabled'])) {
            $output['enabled'] = $this->enabled;
        }
        if (isset($this->_usedProperties['retryStrategy'])) {
            $output['retry_strategy'] = $this->retryStrategy;
        }
        if (isset($this->_usedProperties['httpCodes'])) {
            $output['http_codes'] = array_map(fn ($v) => $v->toArray(), $this->httpCodes);
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
