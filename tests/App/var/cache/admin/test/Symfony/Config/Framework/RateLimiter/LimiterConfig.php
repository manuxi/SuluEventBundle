<?php

namespace Symfony\Config\Framework\RateLimiter;

require_once __DIR__.\DIRECTORY_SEPARATOR.'LimiterConfig'.\DIRECTORY_SEPARATOR.'RateConfig.php';

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class LimiterConfig 
{
    private $lockFactory;
    private $cachePool;
    private $storageService;
    private $policy;
    private $limiters;
    private $limit;
    private $interval;
    private $rate;
    private $_usedProperties = [];

    /**
     * The service ID of the lock factory used by this limiter (or null to disable locking).
     * @default 'auto'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function lockFactory($value): static
    {
        $this->_usedProperties['lockFactory'] = true;
        $this->lockFactory = $value;

        return $this;
    }

    /**
     * The cache pool to use for storing the current limiter state.
     * @default 'cache.rate_limiter'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function cachePool($value): static
    {
        $this->_usedProperties['cachePool'] = true;
        $this->cachePool = $value;

        return $this;
    }

    /**
     * The service ID of a custom storage implementation, this precedes any configured "cache_pool".
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function storageService($value): static
    {
        $this->_usedProperties['storageService'] = true;
        $this->storageService = $value;

        return $this;
    }

    /**
     * The algorithm to be used by this limiter.
     * @default null
     * @param ParamConfigurator|'fixed_window'|'token_bucket'|'sliding_window'|'compound'|'no_limit' $value
     * @return $this
     */
    public function policy($value): static
    {
        $this->_usedProperties['policy'] = true;
        $this->policy = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed>|mixed $value
     *
     * @return $this
     */
    public function limiters(mixed $value): static
    {
        $this->_usedProperties['limiters'] = true;
        $this->limiters = $value;

        return $this;
    }

    /**
     * The maximum allowed hits in a fixed interval or burst.
     * @default null
     * @param ParamConfigurator|int $value
     * @return $this
     */
    public function limit($value): static
    {
        $this->_usedProperties['limit'] = true;
        $this->limit = $value;

        return $this;
    }

    /**
     * Configures the fixed interval if "policy" is set to "fixed_window" or "sliding_window". The value must be a number followed by "second", "minute", "hour", "day", "week" or "month" (or their plural equivalent).
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function interval($value): static
    {
        $this->_usedProperties['interval'] = true;
        $this->interval = $value;

        return $this;
    }

    /**
     * Configures the fill rate if "policy" is set to "token_bucket".
     */
    public function rate(array $value = []): \Symfony\Config\Framework\RateLimiter\LimiterConfig\RateConfig
    {
        if (null === $this->rate) {
            $this->_usedProperties['rate'] = true;
            $this->rate = new \Symfony\Config\Framework\RateLimiter\LimiterConfig\RateConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "rate()" has already been initialized. You cannot pass values the second time you call rate().');
        }

        return $this->rate;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('lock_factory', $config)) {
            $this->_usedProperties['lockFactory'] = true;
            $this->lockFactory = $config['lock_factory'];
            unset($config['lock_factory']);
        }

        if (array_key_exists('cache_pool', $config)) {
            $this->_usedProperties['cachePool'] = true;
            $this->cachePool = $config['cache_pool'];
            unset($config['cache_pool']);
        }

        if (array_key_exists('storage_service', $config)) {
            $this->_usedProperties['storageService'] = true;
            $this->storageService = $config['storage_service'];
            unset($config['storage_service']);
        }

        if (array_key_exists('policy', $config)) {
            $this->_usedProperties['policy'] = true;
            $this->policy = $config['policy'];
            unset($config['policy']);
        }

        if (array_key_exists('limiters', $config)) {
            $this->_usedProperties['limiters'] = true;
            $this->limiters = $config['limiters'];
            unset($config['limiters']);
        }

        if (array_key_exists('limit', $config)) {
            $this->_usedProperties['limit'] = true;
            $this->limit = $config['limit'];
            unset($config['limit']);
        }

        if (array_key_exists('interval', $config)) {
            $this->_usedProperties['interval'] = true;
            $this->interval = $config['interval'];
            unset($config['interval']);
        }

        if (array_key_exists('rate', $config)) {
            $this->_usedProperties['rate'] = true;
            $this->rate = new \Symfony\Config\Framework\RateLimiter\LimiterConfig\RateConfig($config['rate']);
            unset($config['rate']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['lockFactory'])) {
            $output['lock_factory'] = $this->lockFactory;
        }
        if (isset($this->_usedProperties['cachePool'])) {
            $output['cache_pool'] = $this->cachePool;
        }
        if (isset($this->_usedProperties['storageService'])) {
            $output['storage_service'] = $this->storageService;
        }
        if (isset($this->_usedProperties['policy'])) {
            $output['policy'] = $this->policy;
        }
        if (isset($this->_usedProperties['limiters'])) {
            $output['limiters'] = $this->limiters;
        }
        if (isset($this->_usedProperties['limit'])) {
            $output['limit'] = $this->limit;
        }
        if (isset($this->_usedProperties['interval'])) {
            $output['interval'] = $this->interval;
        }
        if (isset($this->_usedProperties['rate'])) {
            $output['rate'] = $this->rate->toArray();
        }

        return $output;
    }

}
