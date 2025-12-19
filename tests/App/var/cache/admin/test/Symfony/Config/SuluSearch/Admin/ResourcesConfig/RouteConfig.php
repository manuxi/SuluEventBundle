<?php

namespace Symfony\Config\SuluSearch\Admin\ResourcesConfig;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class RouteConfig 
{
    private $name;
    private $resultToRouteName;
    private $resultToRoute;
    private $_usedProperties = [];

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function name($value): static
    {
        $this->_usedProperties['name'] = true;
        $this->name = $value;

        return $this;
    }

    /**
     * @return $this
     */
    public function resultToRouteName(string $name, mixed $value): static
    {
        $this->_usedProperties['resultToRouteName'] = true;
        $this->resultToRouteName[$name] = $value;

        return $this;
    }

    /**
     * @return $this
     */
    public function resultToRoute(string $name, mixed $value): static
    {
        $this->_usedProperties['resultToRoute'] = true;
        $this->resultToRoute[$name] = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('name', $config)) {
            $this->_usedProperties['name'] = true;
            $this->name = $config['name'];
            unset($config['name']);
        }

        if (array_key_exists('resultToRouteName', $config)) {
            $this->_usedProperties['resultToRouteName'] = true;
            $this->resultToRouteName = $config['resultToRouteName'];
            unset($config['resultToRouteName']);
        }

        if (array_key_exists('resultToRoute', $config)) {
            $this->_usedProperties['resultToRoute'] = true;
            $this->resultToRoute = $config['resultToRoute'];
            unset($config['resultToRoute']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['name'])) {
            $output['name'] = $this->name;
        }
        if (isset($this->_usedProperties['resultToRouteName'])) {
            $output['resultToRouteName'] = $this->resultToRouteName;
        }
        if (isset($this->_usedProperties['resultToRoute'])) {
            $output['resultToRoute'] = $this->resultToRoute;
        }

        return $output;
    }

}
