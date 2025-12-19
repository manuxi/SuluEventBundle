<?php

namespace Symfony\Config\FosHttpCache\ProxyClient\Fastly;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class HttpConfig 
{
    private $servers;
    private $baseUrl;
    private $httpClient;
    private $_usedProperties = [];

    /**
     * @return $this
     */
    public function servers(string $name, mixed $value): static
    {
        $this->_usedProperties['servers'] = true;
        $this->servers[$name] = $value;

        return $this;
    }

    /**
     * Default host name and optional path for path based invalidation.
     * @default 'service'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function baseUrl($value): static
    {
        $this->_usedProperties['baseUrl'] = true;
        $this->baseUrl = $value;

        return $this;
    }

    /**
     * Httplug async client service name to use for sending the requests.
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function httpClient($value): static
    {
        $this->_usedProperties['httpClient'] = true;
        $this->httpClient = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('servers', $config)) {
            $this->_usedProperties['servers'] = true;
            $this->servers = $config['servers'];
            unset($config['servers']);
        }

        if (array_key_exists('base_url', $config)) {
            $this->_usedProperties['baseUrl'] = true;
            $this->baseUrl = $config['base_url'];
            unset($config['base_url']);
        }

        if (array_key_exists('http_client', $config)) {
            $this->_usedProperties['httpClient'] = true;
            $this->httpClient = $config['http_client'];
            unset($config['http_client']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['servers'])) {
            $output['servers'] = $this->servers;
        }
        if (isset($this->_usedProperties['baseUrl'])) {
            $output['base_url'] = $this->baseUrl;
        }
        if (isset($this->_usedProperties['httpClient'])) {
            $output['http_client'] = $this->httpClient;
        }

        return $output;
    }

}
