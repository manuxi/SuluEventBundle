<?php

namespace Symfony\Config\SuluSearch;

require_once __DIR__.\DIRECTORY_SEPARATOR.'Admin'.\DIRECTORY_SEPARATOR.'ResourcesConfig.php';

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class AdminConfig 
{
    private $resources;
    private $_usedProperties = [];

    public function resources(array $value = []): \Symfony\Config\SuluSearch\Admin\ResourcesConfig
    {
        $this->_usedProperties['resources'] = true;

        return $this->resources[] = new \Symfony\Config\SuluSearch\Admin\ResourcesConfig($value);
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('resources', $config)) {
            $this->_usedProperties['resources'] = true;
            $this->resources = array_map(fn ($v) => new \Symfony\Config\SuluSearch\Admin\ResourcesConfig($v), $config['resources']);
            unset($config['resources']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['resources'])) {
            $output['resources'] = array_map(fn ($v) => $v->toArray(), $this->resources);
        }

        return $output;
    }

}
