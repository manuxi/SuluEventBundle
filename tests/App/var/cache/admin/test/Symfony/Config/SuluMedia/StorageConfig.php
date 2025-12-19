<?php

namespace Symfony\Config\SuluMedia;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class StorageConfig 
{
    private $flysystemService;
    private $segments;
    private $_usedProperties = [];

    /**
     * Service to use for storing media.
     * @default 'default.storage'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function flysystemService($value): static
    {
        $this->_usedProperties['flysystemService'] = true;
        $this->flysystemService = $value;

        return $this;
    }

    /**
     * @default 10
     * @param ParamConfigurator|int $value
     * @return $this
     */
    public function segments($value): static
    {
        $this->_usedProperties['segments'] = true;
        $this->segments = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('flysystem_service', $config)) {
            $this->_usedProperties['flysystemService'] = true;
            $this->flysystemService = $config['flysystem_service'];
            unset($config['flysystem_service']);
        }

        if (array_key_exists('segments', $config)) {
            $this->_usedProperties['segments'] = true;
            $this->segments = $config['segments'];
            unset($config['segments']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['flysystemService'])) {
            $output['flysystem_service'] = $this->flysystemService;
        }
        if (isset($this->_usedProperties['segments'])) {
            $output['segments'] = $this->segments;
        }

        return $output;
    }

}
