<?php

namespace Symfony\Config\SuluAdmin;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class TemplatesConfig 
{
    private $defaultType;
    private $directories;
    private $_usedProperties = [];

    /**
     * @example default
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function defaultType($value): static
    {
        $this->_usedProperties['defaultType'] = true;
        $this->defaultType = $value;

        return $this;
    }

    /**
     * @return $this
     */
    public function directories(string $name, mixed $value): static
    {
        $this->_usedProperties['directories'] = true;
        $this->directories[$name] = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('default_type', $config)) {
            $this->_usedProperties['defaultType'] = true;
            $this->defaultType = $config['default_type'];
            unset($config['default_type']);
        }

        if (array_key_exists('directories', $config)) {
            $this->_usedProperties['directories'] = true;
            $this->directories = $config['directories'];
            unset($config['directories']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['defaultType'])) {
            $output['default_type'] = $this->defaultType;
        }
        if (isset($this->_usedProperties['directories'])) {
            $output['directories'] = $this->directories;
        }

        return $output;
    }

}
