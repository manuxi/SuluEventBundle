<?php

namespace Symfony\Config\SuluAdmin;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class CollaborationConfig 
{
    private $enabled;
    private $interval;
    private $threshold;
    private $_usedProperties = [];

    /**
     * @default false
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
     * The seconds between the keep alive messages for the collaboration feature
     * @default 20
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function interval($value): static
    {
        $this->_usedProperties['interval'] = true;
        $this->interval = $value;

        return $this;
    }

    /**
     * The time after which a collabaration without keep alive signal is terminated
     * @default 60
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function threshold($value): static
    {
        $this->_usedProperties['threshold'] = true;
        $this->threshold = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('enabled', $config)) {
            $this->_usedProperties['enabled'] = true;
            $this->enabled = $config['enabled'];
            unset($config['enabled']);
        }

        if (array_key_exists('interval', $config)) {
            $this->_usedProperties['interval'] = true;
            $this->interval = $config['interval'];
            unset($config['interval']);
        }

        if (array_key_exists('threshold', $config)) {
            $this->_usedProperties['threshold'] = true;
            $this->threshold = $config['threshold'];
            unset($config['threshold']);
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
        if (isset($this->_usedProperties['interval'])) {
            $output['interval'] = $this->interval;
        }
        if (isset($this->_usedProperties['threshold'])) {
            $output['threshold'] = $this->threshold;
        }

        return $output;
    }

}
