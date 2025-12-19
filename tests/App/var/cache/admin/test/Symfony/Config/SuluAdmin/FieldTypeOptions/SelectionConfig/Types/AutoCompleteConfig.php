<?php

namespace Symfony\Config\SuluAdmin\FieldTypeOptions\SelectionConfig\Types;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class AutoCompleteConfig 
{
    private $allowAdd;
    private $idProperty;
    private $displayProperty;
    private $filterParameter;
    private $searchProperties;
    private $_usedProperties = [];

    /**
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function allowAdd($value): static
    {
        $this->_usedProperties['allowAdd'] = true;
        $this->allowAdd = $value;

        return $this;
    }

    /**
     * @default 'id'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function idProperty($value): static
    {
        $this->_usedProperties['idProperty'] = true;
        $this->idProperty = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function displayProperty($value): static
    {
        $this->_usedProperties['displayProperty'] = true;
        $this->displayProperty = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function filterParameter($value): static
    {
        $this->_usedProperties['filterParameter'] = true;
        $this->filterParameter = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed> $value
     *
     * @return $this
     */
    public function searchProperties(ParamConfigurator|array $value): static
    {
        $this->_usedProperties['searchProperties'] = true;
        $this->searchProperties = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('allow_add', $config)) {
            $this->_usedProperties['allowAdd'] = true;
            $this->allowAdd = $config['allow_add'];
            unset($config['allow_add']);
        }

        if (array_key_exists('id_property', $config)) {
            $this->_usedProperties['idProperty'] = true;
            $this->idProperty = $config['id_property'];
            unset($config['id_property']);
        }

        if (array_key_exists('display_property', $config)) {
            $this->_usedProperties['displayProperty'] = true;
            $this->displayProperty = $config['display_property'];
            unset($config['display_property']);
        }

        if (array_key_exists('filter_parameter', $config)) {
            $this->_usedProperties['filterParameter'] = true;
            $this->filterParameter = $config['filter_parameter'];
            unset($config['filter_parameter']);
        }

        if (array_key_exists('search_properties', $config)) {
            $this->_usedProperties['searchProperties'] = true;
            $this->searchProperties = $config['search_properties'];
            unset($config['search_properties']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['allowAdd'])) {
            $output['allow_add'] = $this->allowAdd;
        }
        if (isset($this->_usedProperties['idProperty'])) {
            $output['id_property'] = $this->idProperty;
        }
        if (isset($this->_usedProperties['displayProperty'])) {
            $output['display_property'] = $this->displayProperty;
        }
        if (isset($this->_usedProperties['filterParameter'])) {
            $output['filter_parameter'] = $this->filterParameter;
        }
        if (isset($this->_usedProperties['searchProperties'])) {
            $output['search_properties'] = $this->searchProperties;
        }

        return $output;
    }

}
