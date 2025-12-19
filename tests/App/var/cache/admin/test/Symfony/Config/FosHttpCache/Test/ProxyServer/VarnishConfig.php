<?php

namespace Symfony\Config\FosHttpCache\Test\ProxyServer;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class VarnishConfig 
{
    private $configFile;
    private $binary;
    private $port;
    private $ip;
    private $_usedProperties = [];

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function configFile($value): static
    {
        $this->_usedProperties['configFile'] = true;
        $this->configFile = $value;

        return $this;
    }

    /**
     * @default 'varnishd'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function binary($value): static
    {
        $this->_usedProperties['binary'] = true;
        $this->binary = $value;

        return $this;
    }

    /**
     * @default 6181
     * @param ParamConfigurator|int $value
     * @return $this
     */
    public function port($value): static
    {
        $this->_usedProperties['port'] = true;
        $this->port = $value;

        return $this;
    }

    /**
     * @default '127.0.0.1'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function ip($value): static
    {
        $this->_usedProperties['ip'] = true;
        $this->ip = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('config_file', $config)) {
            $this->_usedProperties['configFile'] = true;
            $this->configFile = $config['config_file'];
            unset($config['config_file']);
        }

        if (array_key_exists('binary', $config)) {
            $this->_usedProperties['binary'] = true;
            $this->binary = $config['binary'];
            unset($config['binary']);
        }

        if (array_key_exists('port', $config)) {
            $this->_usedProperties['port'] = true;
            $this->port = $config['port'];
            unset($config['port']);
        }

        if (array_key_exists('ip', $config)) {
            $this->_usedProperties['ip'] = true;
            $this->ip = $config['ip'];
            unset($config['ip']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['configFile'])) {
            $output['config_file'] = $this->configFile;
        }
        if (isset($this->_usedProperties['binary'])) {
            $output['binary'] = $this->binary;
        }
        if (isset($this->_usedProperties['port'])) {
            $output['port'] = $this->port;
        }
        if (isset($this->_usedProperties['ip'])) {
            $output['ip'] = $this->ip;
        }

        return $output;
    }

}
