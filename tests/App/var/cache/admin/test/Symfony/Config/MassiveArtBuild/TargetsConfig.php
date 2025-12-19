<?php

namespace Symfony\Config\MassiveArtBuild;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class TargetsConfig 
{
    private $dependencies;
    private $_usedProperties = [];

    /**
     * @return $this
     */
    public function dependencies(string $key, ParamConfigurator|array $value): static
    {
        $this->_usedProperties['dependencies'] = true;
        $this->dependencies[$key] = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('dependencies', $config)) {
            $this->_usedProperties['dependencies'] = true;
            $this->dependencies = $config['dependencies'];
            unset($config['dependencies']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['dependencies'])) {
            $output['dependencies'] = $this->dependencies;
        }

        return $output;
    }

}
