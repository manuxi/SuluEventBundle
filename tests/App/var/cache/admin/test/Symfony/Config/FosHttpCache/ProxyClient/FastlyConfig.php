<?php

namespace Symfony\Config\FosHttpCache\ProxyClient;

require_once __DIR__.\DIRECTORY_SEPARATOR.'Fastly'.\DIRECTORY_SEPARATOR.'HttpConfig.php';

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class FastlyConfig 
{
    private $serviceIdentifier;
    private $authenticationToken;
    private $softPurge;
    private $http;
    private $_usedProperties = [];

    /**
     * Identifier for your Fastly service account.
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function serviceIdentifier($value): static
    {
        $this->_usedProperties['serviceIdentifier'] = true;
        $this->serviceIdentifier = $value;

        return $this;
    }

    /**
     * User token for authentication against Fastly APIs.
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function authenticationToken($value): static
    {
        $this->_usedProperties['authenticationToken'] = true;
        $this->authenticationToken = $value;

        return $this;
    }

    /**
     * Boolean for doing soft purges or not on tag & URL purging. Soft purges expires the cache unlike hard purge (removal), and allow grace/stale handling within Fastly VCL.
     * @default true
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function softPurge($value): static
    {
        $this->_usedProperties['softPurge'] = true;
        $this->softPurge = $value;

        return $this;
    }

    /**
     * @default {"servers":["https:\/\/api.fastly.com"],"base_url":"service","http_client":null}
     */
    public function http(array $value = []): \Symfony\Config\FosHttpCache\ProxyClient\Fastly\HttpConfig
    {
        if (null === $this->http) {
            $this->_usedProperties['http'] = true;
            $this->http = new \Symfony\Config\FosHttpCache\ProxyClient\Fastly\HttpConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "http()" has already been initialized. You cannot pass values the second time you call http().');
        }

        return $this->http;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('service_identifier', $config)) {
            $this->_usedProperties['serviceIdentifier'] = true;
            $this->serviceIdentifier = $config['service_identifier'];
            unset($config['service_identifier']);
        }

        if (array_key_exists('authentication_token', $config)) {
            $this->_usedProperties['authenticationToken'] = true;
            $this->authenticationToken = $config['authentication_token'];
            unset($config['authentication_token']);
        }

        if (array_key_exists('soft_purge', $config)) {
            $this->_usedProperties['softPurge'] = true;
            $this->softPurge = $config['soft_purge'];
            unset($config['soft_purge']);
        }

        if (array_key_exists('http', $config)) {
            $this->_usedProperties['http'] = true;
            $this->http = new \Symfony\Config\FosHttpCache\ProxyClient\Fastly\HttpConfig($config['http']);
            unset($config['http']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['serviceIdentifier'])) {
            $output['service_identifier'] = $this->serviceIdentifier;
        }
        if (isset($this->_usedProperties['authenticationToken'])) {
            $output['authentication_token'] = $this->authenticationToken;
        }
        if (isset($this->_usedProperties['softPurge'])) {
            $output['soft_purge'] = $this->softPurge;
        }
        if (isset($this->_usedProperties['http'])) {
            $output['http'] = $this->http->toArray();
        }

        return $output;
    }

}
