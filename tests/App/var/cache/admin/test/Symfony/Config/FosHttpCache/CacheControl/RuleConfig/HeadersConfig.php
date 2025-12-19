<?php

namespace Symfony\Config\FosHttpCache\CacheControl\RuleConfig;

require_once __DIR__.\DIRECTORY_SEPARATOR.'Headers'.\DIRECTORY_SEPARATOR.'CacheControlConfig.php';

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class HeadersConfig 
{
    private $overwrite;
    private $cacheControl;
    private $etag;
    private $lastModified;
    private $reverseProxyTtl;
    private $vary;
    private $_usedProperties = [];

    /**
     * Whether to overwrite cache headers for this rule, defaults to the cache_control.defaults.overwrite setting
     * @default 'default'
     * @param ParamConfigurator|'default'|true|false $value
     * @return $this
     */
    public function overwrite($value): static
    {
        $this->_usedProperties['overwrite'] = true;
        $this->overwrite = $value;

        return $this;
    }

    /**
     * Add the specified cache control directives.
     */
    public function cacheControl(array $value = []): \Symfony\Config\FosHttpCache\CacheControl\RuleConfig\Headers\CacheControlConfig
    {
        if (null === $this->cacheControl) {
            $this->_usedProperties['cacheControl'] = true;
            $this->cacheControl = new \Symfony\Config\FosHttpCache\CacheControl\RuleConfig\Headers\CacheControlConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "cacheControl()" has already been initialized. You cannot pass values the second time you call cacheControl().');
        }

        return $this->cacheControl;
    }

    /**
     * Set a simple ETag which is just the md5 hash of the response body. You can specify which type of ETag you want by passing "strong" or "weak".
     * @default false
     * @param ParamConfigurator|'weak'|'strong'|false $value
     * @return $this
     */
    public function etag($value): static
    {
        $this->_usedProperties['etag'] = true;
        $this->etag = $value;

        return $this;
    }

    /**
     * Set a default last modified timestamp if none is set yet. Value must be parseable by DateTime
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function lastModified($value): static
    {
        $this->_usedProperties['lastModified'] = true;
        $this->lastModified = $value;

        return $this;
    }

    /**
     * Specify a custom time to live in seconds for your caching proxy. This value is sent in the custom header configured in cache_control.ttl_header.
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function reverseProxyTtl($value): static
    {
        $this->_usedProperties['reverseProxyTtl'] = true;
        $this->reverseProxyTtl = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed>|string $value
     *
     * @return $this
     */
    public function vary(ParamConfigurator|string|array $value): static
    {
        $this->_usedProperties['vary'] = true;
        $this->vary = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('overwrite', $config)) {
            $this->_usedProperties['overwrite'] = true;
            $this->overwrite = $config['overwrite'];
            unset($config['overwrite']);
        }

        if (array_key_exists('cache_control', $config)) {
            $this->_usedProperties['cacheControl'] = true;
            $this->cacheControl = new \Symfony\Config\FosHttpCache\CacheControl\RuleConfig\Headers\CacheControlConfig($config['cache_control']);
            unset($config['cache_control']);
        }

        if (array_key_exists('etag', $config)) {
            $this->_usedProperties['etag'] = true;
            $this->etag = $config['etag'];
            unset($config['etag']);
        }

        if (array_key_exists('last_modified', $config)) {
            $this->_usedProperties['lastModified'] = true;
            $this->lastModified = $config['last_modified'];
            unset($config['last_modified']);
        }

        if (array_key_exists('reverse_proxy_ttl', $config)) {
            $this->_usedProperties['reverseProxyTtl'] = true;
            $this->reverseProxyTtl = $config['reverse_proxy_ttl'];
            unset($config['reverse_proxy_ttl']);
        }

        if (array_key_exists('vary', $config)) {
            $this->_usedProperties['vary'] = true;
            $this->vary = $config['vary'];
            unset($config['vary']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['overwrite'])) {
            $output['overwrite'] = $this->overwrite;
        }
        if (isset($this->_usedProperties['cacheControl'])) {
            $output['cache_control'] = $this->cacheControl->toArray();
        }
        if (isset($this->_usedProperties['etag'])) {
            $output['etag'] = $this->etag;
        }
        if (isset($this->_usedProperties['lastModified'])) {
            $output['last_modified'] = $this->lastModified;
        }
        if (isset($this->_usedProperties['reverseProxyTtl'])) {
            $output['reverse_proxy_ttl'] = $this->reverseProxyTtl;
        }
        if (isset($this->_usedProperties['vary'])) {
            $output['vary'] = $this->vary;
        }

        return $output;
    }

}
