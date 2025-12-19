<?php

namespace Symfony\Config\SuluSearch\Admin;

require_once __DIR__.\DIRECTORY_SEPARATOR.'ResourcesConfig'.\DIRECTORY_SEPARATOR.'RouteConfig.php';

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class ResourcesConfig 
{
    private $name;
    private $icon;
    private $route;
    private $securityContext;
    private $contexts;
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
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function icon($value): static
    {
        $this->_usedProperties['icon'] = true;
        $this->icon = $value;

        return $this;
    }

    public function route(array $value = []): \Symfony\Config\SuluSearch\Admin\ResourcesConfig\RouteConfig
    {
        if (null === $this->route) {
            $this->_usedProperties['route'] = true;
            $this->route = new \Symfony\Config\SuluSearch\Admin\ResourcesConfig\RouteConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "route()" has already been initialized. You cannot pass values the second time you call route().');
        }

        return $this->route;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function securityContext($value): static
    {
        $this->_usedProperties['securityContext'] = true;
        $this->securityContext = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed> $value
     *
     * @return $this
     */
    public function contexts(ParamConfigurator|array $value): static
    {
        $this->_usedProperties['contexts'] = true;
        $this->contexts = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('name', $config)) {
            $this->_usedProperties['name'] = true;
            $this->name = $config['name'];
            unset($config['name']);
        }

        if (array_key_exists('icon', $config)) {
            $this->_usedProperties['icon'] = true;
            $this->icon = $config['icon'];
            unset($config['icon']);
        }

        if (array_key_exists('route', $config)) {
            $this->_usedProperties['route'] = true;
            $this->route = new \Symfony\Config\SuluSearch\Admin\ResourcesConfig\RouteConfig($config['route']);
            unset($config['route']);
        }

        if (array_key_exists('securityContext', $config)) {
            $this->_usedProperties['securityContext'] = true;
            $this->securityContext = $config['securityContext'];
            unset($config['securityContext']);
        }

        if (array_key_exists('contexts', $config)) {
            $this->_usedProperties['contexts'] = true;
            $this->contexts = $config['contexts'];
            unset($config['contexts']);
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
        if (isset($this->_usedProperties['icon'])) {
            $output['icon'] = $this->icon;
        }
        if (isset($this->_usedProperties['route'])) {
            $output['route'] = $this->route->toArray();
        }
        if (isset($this->_usedProperties['securityContext'])) {
            $output['securityContext'] = $this->securityContext;
        }
        if (isset($this->_usedProperties['contexts'])) {
            $output['contexts'] = $this->contexts;
        }

        return $output;
    }

}
