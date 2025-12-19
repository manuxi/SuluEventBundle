<?php

namespace Symfony\Config\SuluAdmin\FieldTypeOptions;

require_once __DIR__.\DIRECTORY_SEPARATOR.'SingleSelectionConfig'.\DIRECTORY_SEPARATOR.'ViewConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'SingleSelectionConfig'.\DIRECTORY_SEPARATOR.'TypesConfig.php';

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class SingleSelectionConfig 
{
    private $defaultType;
    private $resourceKey;
    private $view;
    private $types;
    private $_usedProperties = [];

    /**
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
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function resourceKey($value): static
    {
        $this->_usedProperties['resourceKey'] = true;
        $this->resourceKey = $value;

        return $this;
    }

    public function view(array $value = []): \Symfony\Config\SuluAdmin\FieldTypeOptions\SingleSelectionConfig\ViewConfig
    {
        if (null === $this->view) {
            $this->_usedProperties['view'] = true;
            $this->view = new \Symfony\Config\SuluAdmin\FieldTypeOptions\SingleSelectionConfig\ViewConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "view()" has already been initialized. You cannot pass values the second time you call view().');
        }

        return $this->view;
    }

    public function types(array $value = []): \Symfony\Config\SuluAdmin\FieldTypeOptions\SingleSelectionConfig\TypesConfig
    {
        if (null === $this->types) {
            $this->_usedProperties['types'] = true;
            $this->types = new \Symfony\Config\SuluAdmin\FieldTypeOptions\SingleSelectionConfig\TypesConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "types()" has already been initialized. You cannot pass values the second time you call types().');
        }

        return $this->types;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('default_type', $config)) {
            $this->_usedProperties['defaultType'] = true;
            $this->defaultType = $config['default_type'];
            unset($config['default_type']);
        }

        if (array_key_exists('resource_key', $config)) {
            $this->_usedProperties['resourceKey'] = true;
            $this->resourceKey = $config['resource_key'];
            unset($config['resource_key']);
        }

        if (array_key_exists('view', $config)) {
            $this->_usedProperties['view'] = true;
            $this->view = new \Symfony\Config\SuluAdmin\FieldTypeOptions\SingleSelectionConfig\ViewConfig($config['view']);
            unset($config['view']);
        }

        if (array_key_exists('types', $config)) {
            $this->_usedProperties['types'] = true;
            $this->types = new \Symfony\Config\SuluAdmin\FieldTypeOptions\SingleSelectionConfig\TypesConfig($config['types']);
            unset($config['types']);
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
        if (isset($this->_usedProperties['resourceKey'])) {
            $output['resource_key'] = $this->resourceKey;
        }
        if (isset($this->_usedProperties['view'])) {
            $output['view'] = $this->view->toArray();
        }
        if (isset($this->_usedProperties['types'])) {
            $output['types'] = $this->types->toArray();
        }

        return $output;
    }

}
