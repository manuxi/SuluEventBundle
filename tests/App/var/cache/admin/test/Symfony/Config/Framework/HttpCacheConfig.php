<?php

namespace Symfony\Config\Framework;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class HttpCacheConfig 
{
    private $enabled;
    private $debug;
    private $traceLevel;
    private $traceHeader;
    private $defaultTtl;
    private $privateHeaders;
    private $skipResponseHeaders;
    private $allowReload;
    private $allowRevalidate;
    private $staleWhileRevalidate;
    private $staleIfError;
    private $terminateOnCacheHit;
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
     * @default '%kernel.debug%'
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function debug($value): static
    {
        $this->_usedProperties['debug'] = true;
        $this->debug = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|'none'|'short'|'full' $value
     * @return $this
     */
    public function traceLevel($value): static
    {
        $this->_usedProperties['traceLevel'] = true;
        $this->traceLevel = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function traceHeader($value): static
    {
        $this->_usedProperties['traceHeader'] = true;
        $this->traceHeader = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|int $value
     * @return $this
     */
    public function defaultTtl($value): static
    {
        $this->_usedProperties['defaultTtl'] = true;
        $this->defaultTtl = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed> $value
     *
     * @return $this
     */
    public function privateHeaders(ParamConfigurator|array $value): static
    {
        $this->_usedProperties['privateHeaders'] = true;
        $this->privateHeaders = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed> $value
     *
     * @return $this
     */
    public function skipResponseHeaders(ParamConfigurator|array $value): static
    {
        $this->_usedProperties['skipResponseHeaders'] = true;
        $this->skipResponseHeaders = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function allowReload($value): static
    {
        $this->_usedProperties['allowReload'] = true;
        $this->allowReload = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function allowRevalidate($value): static
    {
        $this->_usedProperties['allowRevalidate'] = true;
        $this->allowRevalidate = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|int $value
     * @return $this
     */
    public function staleWhileRevalidate($value): static
    {
        $this->_usedProperties['staleWhileRevalidate'] = true;
        $this->staleWhileRevalidate = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|int $value
     * @return $this
     */
    public function staleIfError($value): static
    {
        $this->_usedProperties['staleIfError'] = true;
        $this->staleIfError = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function terminateOnCacheHit($value): static
    {
        $this->_usedProperties['terminateOnCacheHit'] = true;
        $this->terminateOnCacheHit = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('enabled', $config)) {
            $this->_usedProperties['enabled'] = true;
            $this->enabled = $config['enabled'];
            unset($config['enabled']);
        }

        if (array_key_exists('debug', $config)) {
            $this->_usedProperties['debug'] = true;
            $this->debug = $config['debug'];
            unset($config['debug']);
        }

        if (array_key_exists('trace_level', $config)) {
            $this->_usedProperties['traceLevel'] = true;
            $this->traceLevel = $config['trace_level'];
            unset($config['trace_level']);
        }

        if (array_key_exists('trace_header', $config)) {
            $this->_usedProperties['traceHeader'] = true;
            $this->traceHeader = $config['trace_header'];
            unset($config['trace_header']);
        }

        if (array_key_exists('default_ttl', $config)) {
            $this->_usedProperties['defaultTtl'] = true;
            $this->defaultTtl = $config['default_ttl'];
            unset($config['default_ttl']);
        }

        if (array_key_exists('private_headers', $config)) {
            $this->_usedProperties['privateHeaders'] = true;
            $this->privateHeaders = $config['private_headers'];
            unset($config['private_headers']);
        }

        if (array_key_exists('skip_response_headers', $config)) {
            $this->_usedProperties['skipResponseHeaders'] = true;
            $this->skipResponseHeaders = $config['skip_response_headers'];
            unset($config['skip_response_headers']);
        }

        if (array_key_exists('allow_reload', $config)) {
            $this->_usedProperties['allowReload'] = true;
            $this->allowReload = $config['allow_reload'];
            unset($config['allow_reload']);
        }

        if (array_key_exists('allow_revalidate', $config)) {
            $this->_usedProperties['allowRevalidate'] = true;
            $this->allowRevalidate = $config['allow_revalidate'];
            unset($config['allow_revalidate']);
        }

        if (array_key_exists('stale_while_revalidate', $config)) {
            $this->_usedProperties['staleWhileRevalidate'] = true;
            $this->staleWhileRevalidate = $config['stale_while_revalidate'];
            unset($config['stale_while_revalidate']);
        }

        if (array_key_exists('stale_if_error', $config)) {
            $this->_usedProperties['staleIfError'] = true;
            $this->staleIfError = $config['stale_if_error'];
            unset($config['stale_if_error']);
        }

        if (array_key_exists('terminate_on_cache_hit', $config)) {
            $this->_usedProperties['terminateOnCacheHit'] = true;
            $this->terminateOnCacheHit = $config['terminate_on_cache_hit'];
            unset($config['terminate_on_cache_hit']);
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
        if (isset($this->_usedProperties['debug'])) {
            $output['debug'] = $this->debug;
        }
        if (isset($this->_usedProperties['traceLevel'])) {
            $output['trace_level'] = $this->traceLevel;
        }
        if (isset($this->_usedProperties['traceHeader'])) {
            $output['trace_header'] = $this->traceHeader;
        }
        if (isset($this->_usedProperties['defaultTtl'])) {
            $output['default_ttl'] = $this->defaultTtl;
        }
        if (isset($this->_usedProperties['privateHeaders'])) {
            $output['private_headers'] = $this->privateHeaders;
        }
        if (isset($this->_usedProperties['skipResponseHeaders'])) {
            $output['skip_response_headers'] = $this->skipResponseHeaders;
        }
        if (isset($this->_usedProperties['allowReload'])) {
            $output['allow_reload'] = $this->allowReload;
        }
        if (isset($this->_usedProperties['allowRevalidate'])) {
            $output['allow_revalidate'] = $this->allowRevalidate;
        }
        if (isset($this->_usedProperties['staleWhileRevalidate'])) {
            $output['stale_while_revalidate'] = $this->staleWhileRevalidate;
        }
        if (isset($this->_usedProperties['staleIfError'])) {
            $output['stale_if_error'] = $this->staleIfError;
        }
        if (isset($this->_usedProperties['terminateOnCacheHit'])) {
            $output['terminate_on_cache_hit'] = $this->terminateOnCacheHit;
        }

        return $output;
    }

}
