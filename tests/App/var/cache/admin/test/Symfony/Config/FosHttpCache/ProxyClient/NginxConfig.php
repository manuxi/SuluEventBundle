<?php

namespace Symfony\Config\FosHttpCache\ProxyClient;

require_once __DIR__.\DIRECTORY_SEPARATOR.'Nginx'.\DIRECTORY_SEPARATOR.'HttpConfig.php';

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class NginxConfig 
{
    private $purgeLocation;
    private $http;
    private $_usedProperties = [];

    /**
     * Path to trigger the purge on Nginx for different location purge.
     * @default false
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function purgeLocation($value): static
    {
        $this->_usedProperties['purgeLocation'] = true;
        $this->purgeLocation = $value;

        return $this;
    }

    public function http(array $value = []): \Symfony\Config\FosHttpCache\ProxyClient\Nginx\HttpConfig
    {
        if (null === $this->http) {
            $this->_usedProperties['http'] = true;
            $this->http = new \Symfony\Config\FosHttpCache\ProxyClient\Nginx\HttpConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "http()" has already been initialized. You cannot pass values the second time you call http().');
        }

        return $this->http;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('purge_location', $config)) {
            $this->_usedProperties['purgeLocation'] = true;
            $this->purgeLocation = $config['purge_location'];
            unset($config['purge_location']);
        }

        if (array_key_exists('http', $config)) {
            $this->_usedProperties['http'] = true;
            $this->http = new \Symfony\Config\FosHttpCache\ProxyClient\Nginx\HttpConfig($config['http']);
            unset($config['http']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['purgeLocation'])) {
            $output['purge_location'] = $this->purgeLocation;
        }
        if (isset($this->_usedProperties['http'])) {
            $output['http'] = $this->http->toArray();
        }

        return $output;
    }

}
