<?php

namespace Symfony\Config\FosHttpCache\ProxyClient;

require_once __DIR__.\DIRECTORY_SEPARATOR.'Symfony'.\DIRECTORY_SEPARATOR.'HttpConfig.php';

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class SymfonyConfig 
{
    private $tagsHeader;
    private $tagsMethod;
    private $headerLength;
    private $purgeMethod;
    private $useKernelDispatcher;
    private $http;
    private $_usedProperties = [];

    /**
     * HTTP header to use when sending tag invalidation requests to Symfony HttpCache
     * @default 'X-Cache-Tags'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function tagsHeader($value): static
    {
        $this->_usedProperties['tagsHeader'] = true;
        $this->tagsHeader = $value;

        return $this;
    }

    /**
     * HTTP method for sending tag invalidation requests to Symfony HttpCache
     * @default 'PURGETAGS'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function tagsMethod($value): static
    {
        $this->_usedProperties['tagsMethod'] = true;
        $this->tagsMethod = $value;

        return $this;
    }

    /**
     * Maximum header length when invalidating tags. If there are more tags to invalidate than fit into the header, the invalidation request is split into several requests.
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function headerLength($value): static
    {
        $this->_usedProperties['headerLength'] = true;
        $this->headerLength = $value;

        return $this;
    }

    /**
     * HTTP method to use when sending purge requests to Symfony HttpCache
     * @default 'PURGE'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function purgeMethod($value): static
    {
        $this->_usedProperties['purgeMethod'] = true;
        $this->purgeMethod = $value;

        return $this;
    }

    /**
     * Dispatches invalidation requests to the kernel directly instead of executing real HTTP requests. Requires special kernel setup! Refer to the documentation for more information.
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function useKernelDispatcher($value): static
    {
        $this->_usedProperties['useKernelDispatcher'] = true;
        $this->useKernelDispatcher = $value;

        return $this;
    }

    public function http(array $value = []): \Symfony\Config\FosHttpCache\ProxyClient\Symfony\HttpConfig
    {
        if (null === $this->http) {
            $this->_usedProperties['http'] = true;
            $this->http = new \Symfony\Config\FosHttpCache\ProxyClient\Symfony\HttpConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "http()" has already been initialized. You cannot pass values the second time you call http().');
        }

        return $this->http;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('tags_header', $config)) {
            $this->_usedProperties['tagsHeader'] = true;
            $this->tagsHeader = $config['tags_header'];
            unset($config['tags_header']);
        }

        if (array_key_exists('tags_method', $config)) {
            $this->_usedProperties['tagsMethod'] = true;
            $this->tagsMethod = $config['tags_method'];
            unset($config['tags_method']);
        }

        if (array_key_exists('header_length', $config)) {
            $this->_usedProperties['headerLength'] = true;
            $this->headerLength = $config['header_length'];
            unset($config['header_length']);
        }

        if (array_key_exists('purge_method', $config)) {
            $this->_usedProperties['purgeMethod'] = true;
            $this->purgeMethod = $config['purge_method'];
            unset($config['purge_method']);
        }

        if (array_key_exists('use_kernel_dispatcher', $config)) {
            $this->_usedProperties['useKernelDispatcher'] = true;
            $this->useKernelDispatcher = $config['use_kernel_dispatcher'];
            unset($config['use_kernel_dispatcher']);
        }

        if (array_key_exists('http', $config)) {
            $this->_usedProperties['http'] = true;
            $this->http = new \Symfony\Config\FosHttpCache\ProxyClient\Symfony\HttpConfig($config['http']);
            unset($config['http']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['tagsHeader'])) {
            $output['tags_header'] = $this->tagsHeader;
        }
        if (isset($this->_usedProperties['tagsMethod'])) {
            $output['tags_method'] = $this->tagsMethod;
        }
        if (isset($this->_usedProperties['headerLength'])) {
            $output['header_length'] = $this->headerLength;
        }
        if (isset($this->_usedProperties['purgeMethod'])) {
            $output['purge_method'] = $this->purgeMethod;
        }
        if (isset($this->_usedProperties['useKernelDispatcher'])) {
            $output['use_kernel_dispatcher'] = $this->useKernelDispatcher;
        }
        if (isset($this->_usedProperties['http'])) {
            $output['http'] = $this->http->toArray();
        }

        return $output;
    }

}
