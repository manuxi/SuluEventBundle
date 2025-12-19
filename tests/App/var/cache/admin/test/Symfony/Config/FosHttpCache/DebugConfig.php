<?php

namespace Symfony\Config\FosHttpCache;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class DebugConfig 
{
    private $enabled;
    private $header;
    private $_usedProperties = [];

    /**
     * Whether to send a debug header with the response to trigger a caching proxy to send debug information. If not set, defaults to kernel.debug.
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
     * The header to send if debug is true.
     * @default 'X-Cache-Debug'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function header($value): static
    {
        $this->_usedProperties['header'] = true;
        $this->header = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('enabled', $config)) {
            $this->_usedProperties['enabled'] = true;
            $this->enabled = $config['enabled'];
            unset($config['enabled']);
        }

        if (array_key_exists('header', $config)) {
            $this->_usedProperties['header'] = true;
            $this->header = $config['header'];
            unset($config['header']);
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
        if (isset($this->_usedProperties['header'])) {
            $output['header'] = $this->header;
        }

        return $output;
    }

}
