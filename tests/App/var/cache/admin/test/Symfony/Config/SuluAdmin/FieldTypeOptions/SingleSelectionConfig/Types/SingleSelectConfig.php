<?php

namespace Symfony\Config\SuluAdmin\FieldTypeOptions\SingleSelectionConfig\Types;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class SingleSelectConfig 
{
    private $displayProperty;
    private $idProperty;
    private $overlayTitle;
    private $_usedProperties = [];

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
    public function overlayTitle($value): static
    {
        $this->_usedProperties['overlayTitle'] = true;
        $this->overlayTitle = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('display_property', $config)) {
            $this->_usedProperties['displayProperty'] = true;
            $this->displayProperty = $config['display_property'];
            unset($config['display_property']);
        }

        if (array_key_exists('id_property', $config)) {
            $this->_usedProperties['idProperty'] = true;
            $this->idProperty = $config['id_property'];
            unset($config['id_property']);
        }

        if (array_key_exists('overlay_title', $config)) {
            $this->_usedProperties['overlayTitle'] = true;
            $this->overlayTitle = $config['overlay_title'];
            unset($config['overlay_title']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['displayProperty'])) {
            $output['display_property'] = $this->displayProperty;
        }
        if (isset($this->_usedProperties['idProperty'])) {
            $output['id_property'] = $this->idProperty;
        }
        if (isset($this->_usedProperties['overlayTitle'])) {
            $output['overlay_title'] = $this->overlayTitle;
        }

        return $output;
    }

}
