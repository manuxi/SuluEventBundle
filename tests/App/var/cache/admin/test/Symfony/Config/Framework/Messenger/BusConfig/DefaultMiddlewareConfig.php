<?php

namespace Symfony\Config\Framework\Messenger\BusConfig;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class DefaultMiddlewareConfig 
{
    private $enabled;
    private $allowNoHandlers;
    private $allowNoSenders;
    private $_usedProperties = [];

    /**
     * @default true
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
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function allowNoHandlers($value): static
    {
        $this->_usedProperties['allowNoHandlers'] = true;
        $this->allowNoHandlers = $value;

        return $this;
    }

    /**
     * @default true
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function allowNoSenders($value): static
    {
        $this->_usedProperties['allowNoSenders'] = true;
        $this->allowNoSenders = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('enabled', $config)) {
            $this->_usedProperties['enabled'] = true;
            $this->enabled = $config['enabled'];
            unset($config['enabled']);
        }

        if (array_key_exists('allow_no_handlers', $config)) {
            $this->_usedProperties['allowNoHandlers'] = true;
            $this->allowNoHandlers = $config['allow_no_handlers'];
            unset($config['allow_no_handlers']);
        }

        if (array_key_exists('allow_no_senders', $config)) {
            $this->_usedProperties['allowNoSenders'] = true;
            $this->allowNoSenders = $config['allow_no_senders'];
            unset($config['allow_no_senders']);
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
        if (isset($this->_usedProperties['allowNoHandlers'])) {
            $output['allow_no_handlers'] = $this->allowNoHandlers;
        }
        if (isset($this->_usedProperties['allowNoSenders'])) {
            $output['allow_no_senders'] = $this->allowNoSenders;
        }

        return $output;
    }

}
