<?php

namespace Symfony\Config\FosHttpCache;

require_once __DIR__.\DIRECTORY_SEPARATOR.'Test'.\DIRECTORY_SEPARATOR.'ProxyServerConfig.php';

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class TestConfig 
{
    private $cacheHeader;
    private $proxyServer;
    private $_usedProperties = [];

    /**
     * HTTP cache hit/miss header
     * @default 'X-Cache'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function cacheHeader($value): static
    {
        $this->_usedProperties['cacheHeader'] = true;
        $this->cacheHeader = $value;

        return $this;
    }

    /**
     * Configure how caching proxy will be run in your tests
     */
    public function proxyServer(array $value = []): \Symfony\Config\FosHttpCache\Test\ProxyServerConfig
    {
        if (null === $this->proxyServer) {
            $this->_usedProperties['proxyServer'] = true;
            $this->proxyServer = new \Symfony\Config\FosHttpCache\Test\ProxyServerConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "proxyServer()" has already been initialized. You cannot pass values the second time you call proxyServer().');
        }

        return $this->proxyServer;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('cache_header', $config)) {
            $this->_usedProperties['cacheHeader'] = true;
            $this->cacheHeader = $config['cache_header'];
            unset($config['cache_header']);
        }

        if (array_key_exists('proxy_server', $config)) {
            $this->_usedProperties['proxyServer'] = true;
            $this->proxyServer = new \Symfony\Config\FosHttpCache\Test\ProxyServerConfig($config['proxy_server']);
            unset($config['proxy_server']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['cacheHeader'])) {
            $output['cache_header'] = $this->cacheHeader;
        }
        if (isset($this->_usedProperties['proxyServer'])) {
            $output['proxy_server'] = $this->proxyServer->toArray();
        }

        return $output;
    }

}
