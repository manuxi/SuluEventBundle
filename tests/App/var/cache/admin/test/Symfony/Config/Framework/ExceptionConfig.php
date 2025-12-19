<?php

namespace Symfony\Config\Framework;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class ExceptionConfig 
{
    private $logLevel;
    private $statusCode;
    private $logChannel;
    private $_usedProperties = [];

    /**
     * The level of log message. Null to let Symfony decide.
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function logLevel($value): static
    {
        $this->_usedProperties['logLevel'] = true;
        $this->logLevel = $value;

        return $this;
    }

    /**
     * The status code of the response. Null or 0 to let Symfony decide.
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function statusCode($value): static
    {
        $this->_usedProperties['statusCode'] = true;
        $this->statusCode = $value;

        return $this;
    }

    /**
     * The channel of log message. Null to let Symfony decide.
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function logChannel($value): static
    {
        $this->_usedProperties['logChannel'] = true;
        $this->logChannel = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('log_level', $config)) {
            $this->_usedProperties['logLevel'] = true;
            $this->logLevel = $config['log_level'];
            unset($config['log_level']);
        }

        if (array_key_exists('status_code', $config)) {
            $this->_usedProperties['statusCode'] = true;
            $this->statusCode = $config['status_code'];
            unset($config['status_code']);
        }

        if (array_key_exists('log_channel', $config)) {
            $this->_usedProperties['logChannel'] = true;
            $this->logChannel = $config['log_channel'];
            unset($config['log_channel']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['logLevel'])) {
            $output['log_level'] = $this->logLevel;
        }
        if (isset($this->_usedProperties['statusCode'])) {
            $output['status_code'] = $this->statusCode;
        }
        if (isset($this->_usedProperties['logChannel'])) {
            $output['log_channel'] = $this->logChannel;
        }

        return $output;
    }

}
