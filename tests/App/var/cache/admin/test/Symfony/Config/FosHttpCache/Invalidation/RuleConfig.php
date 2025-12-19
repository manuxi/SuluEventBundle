<?php

namespace Symfony\Config\FosHttpCache\Invalidation;

require_once __DIR__.\DIRECTORY_SEPARATOR.'RuleConfig'.\DIRECTORY_SEPARATOR.'MatchConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'RuleConfig'.\DIRECTORY_SEPARATOR.'RouteConfig.php';

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class RuleConfig 
{
    private $match;
    private $routes;
    private $_usedProperties = [];

    public function match(array $value = []): \Symfony\Config\FosHttpCache\Invalidation\RuleConfig\MatchConfig
    {
        if (null === $this->match) {
            $this->_usedProperties['match'] = true;
            $this->match = new \Symfony\Config\FosHttpCache\Invalidation\RuleConfig\MatchConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "match()" has already been initialized. You cannot pass values the second time you call match().');
        }

        return $this->match;
    }

    /**
     * Target routes to invalidate when request is matched
     */
    public function route(string $name, array $value = []): \Symfony\Config\FosHttpCache\Invalidation\RuleConfig\RouteConfig
    {
        if (!isset($this->routes[$name])) {
            $this->_usedProperties['routes'] = true;
            $this->routes[$name] = new \Symfony\Config\FosHttpCache\Invalidation\RuleConfig\RouteConfig($value);
        } elseif (1 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "route()" has already been initialized. You cannot pass values the second time you call route().');
        }

        return $this->routes[$name];
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('match', $config)) {
            $this->_usedProperties['match'] = true;
            $this->match = new \Symfony\Config\FosHttpCache\Invalidation\RuleConfig\MatchConfig($config['match']);
            unset($config['match']);
        }

        if (array_key_exists('routes', $config)) {
            $this->_usedProperties['routes'] = true;
            $this->routes = array_map(fn ($v) => new \Symfony\Config\FosHttpCache\Invalidation\RuleConfig\RouteConfig($v), $config['routes']);
            unset($config['routes']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['match'])) {
            $output['match'] = $this->match->toArray();
        }
        if (isset($this->_usedProperties['routes'])) {
            $output['routes'] = array_map(fn ($v) => $v->toArray(), $this->routes);
        }

        return $output;
    }

}
