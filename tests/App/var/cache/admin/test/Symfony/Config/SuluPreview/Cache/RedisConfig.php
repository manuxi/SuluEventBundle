<?php

namespace Symfony\Config\SuluPreview\Cache;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class RedisConfig 
{
    private $connectionId;
    private $host;
    private $port;
    private $password;
    private $timeout;
    private $database;
    private $_usedProperties = [];

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function connectionId($value): static
    {
        $this->_usedProperties['connectionId'] = true;
        $this->connectionId = $value;

        return $this;
    }

    /**
     * @default '127.0.0.1'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function host($value): static
    {
        $this->_usedProperties['host'] = true;
        $this->host = $value;

        return $this;
    }

    /**
     * @default '6379'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function port($value): static
    {
        $this->_usedProperties['port'] = true;
        $this->port = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function password($value): static
    {
        $this->_usedProperties['password'] = true;
        $this->password = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function timeout($value): static
    {
        $this->_usedProperties['timeout'] = true;
        $this->timeout = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function database($value): static
    {
        $this->_usedProperties['database'] = true;
        $this->database = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('connection_id', $config)) {
            $this->_usedProperties['connectionId'] = true;
            $this->connectionId = $config['connection_id'];
            unset($config['connection_id']);
        }

        if (array_key_exists('host', $config)) {
            $this->_usedProperties['host'] = true;
            $this->host = $config['host'];
            unset($config['host']);
        }

        if (array_key_exists('port', $config)) {
            $this->_usedProperties['port'] = true;
            $this->port = $config['port'];
            unset($config['port']);
        }

        if (array_key_exists('password', $config)) {
            $this->_usedProperties['password'] = true;
            $this->password = $config['password'];
            unset($config['password']);
        }

        if (array_key_exists('timeout', $config)) {
            $this->_usedProperties['timeout'] = true;
            $this->timeout = $config['timeout'];
            unset($config['timeout']);
        }

        if (array_key_exists('database', $config)) {
            $this->_usedProperties['database'] = true;
            $this->database = $config['database'];
            unset($config['database']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['connectionId'])) {
            $output['connection_id'] = $this->connectionId;
        }
        if (isset($this->_usedProperties['host'])) {
            $output['host'] = $this->host;
        }
        if (isset($this->_usedProperties['port'])) {
            $output['port'] = $this->port;
        }
        if (isset($this->_usedProperties['password'])) {
            $output['password'] = $this->password;
        }
        if (isset($this->_usedProperties['timeout'])) {
            $output['timeout'] = $this->timeout;
        }
        if (isset($this->_usedProperties['database'])) {
            $output['database'] = $this->database;
        }

        return $output;
    }

}
