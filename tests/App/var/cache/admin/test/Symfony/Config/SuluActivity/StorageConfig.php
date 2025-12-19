<?php

namespace Symfony\Config\SuluActivity;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class StorageConfig 
{
    private $adapter;
    private $persistPayload;
    private $_usedProperties = [];

    /**
     * @default 'doctrine'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function adapter($value): static
    {
        $this->_usedProperties['adapter'] = true;
        $this->adapter = $value;

        return $this;
    }

    /**
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function persistPayload($value): static
    {
        $this->_usedProperties['persistPayload'] = true;
        $this->persistPayload = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('adapter', $config)) {
            $this->_usedProperties['adapter'] = true;
            $this->adapter = $config['adapter'];
            unset($config['adapter']);
        }

        if (array_key_exists('persist_payload', $config)) {
            $this->_usedProperties['persistPayload'] = true;
            $this->persistPayload = $config['persist_payload'];
            unset($config['persist_payload']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['adapter'])) {
            $output['adapter'] = $this->adapter;
        }
        if (isset($this->_usedProperties['persistPayload'])) {
            $output['persist_payload'] = $this->persistPayload;
        }

        return $output;
    }

}
