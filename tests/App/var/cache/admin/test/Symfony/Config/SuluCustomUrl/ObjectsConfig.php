<?php

namespace Symfony\Config\SuluCustomUrl;

require_once __DIR__.\DIRECTORY_SEPARATOR.'Objects'.\DIRECTORY_SEPARATOR.'CustomUrlConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Objects'.\DIRECTORY_SEPARATOR.'CustomUrlRouteConfig.php';

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class ObjectsConfig 
{
    private $customUrl;
    private $customUrlRoute;
    private $_usedProperties = [];

    /**
     * @default {"model":"Sulu\\CustomUrl\\Domain\\Model\\CustomUrl"}
     */
    public function customUrl(array $value = []): \Symfony\Config\SuluCustomUrl\Objects\CustomUrlConfig
    {
        if (null === $this->customUrl) {
            $this->_usedProperties['customUrl'] = true;
            $this->customUrl = new \Symfony\Config\SuluCustomUrl\Objects\CustomUrlConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "customUrl()" has already been initialized. You cannot pass values the second time you call customUrl().');
        }

        return $this->customUrl;
    }

    /**
     * @default {"model":"Sulu\\CustomUrl\\Domain\\Model\\CustomUrlRoute"}
     */
    public function customUrlRoute(array $value = []): \Symfony\Config\SuluCustomUrl\Objects\CustomUrlRouteConfig
    {
        if (null === $this->customUrlRoute) {
            $this->_usedProperties['customUrlRoute'] = true;
            $this->customUrlRoute = new \Symfony\Config\SuluCustomUrl\Objects\CustomUrlRouteConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "customUrlRoute()" has already been initialized. You cannot pass values the second time you call customUrlRoute().');
        }

        return $this->customUrlRoute;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('custom_url', $config)) {
            $this->_usedProperties['customUrl'] = true;
            $this->customUrl = new \Symfony\Config\SuluCustomUrl\Objects\CustomUrlConfig($config['custom_url']);
            unset($config['custom_url']);
        }

        if (array_key_exists('custom_url_route', $config)) {
            $this->_usedProperties['customUrlRoute'] = true;
            $this->customUrlRoute = new \Symfony\Config\SuluCustomUrl\Objects\CustomUrlRouteConfig($config['custom_url_route']);
            unset($config['custom_url_route']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['customUrl'])) {
            $output['custom_url'] = $this->customUrl->toArray();
        }
        if (isset($this->_usedProperties['customUrlRoute'])) {
            $output['custom_url_route'] = $this->customUrlRoute->toArray();
        }

        return $output;
    }

}
