<?php

namespace Symfony\Config\SuluEvent;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class RoutingConfig 
{
    private $routeSchema;
    private $_usedProperties = [];

    /**
     * @default '/{parent}/{object.getTitle()}'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function routeSchema($value): static
    {
        $this->_usedProperties['routeSchema'] = true;
        $this->routeSchema = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('route_schema', $config)) {
            $this->_usedProperties['routeSchema'] = true;
            $this->routeSchema = $config['route_schema'];
            unset($config['route_schema']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['routeSchema'])) {
            $output['route_schema'] = $this->routeSchema;
        }

        return $output;
    }

}
