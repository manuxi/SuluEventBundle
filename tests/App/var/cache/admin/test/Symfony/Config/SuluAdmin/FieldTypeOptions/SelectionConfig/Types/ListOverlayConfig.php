<?php

namespace Symfony\Config\SuluAdmin\FieldTypeOptions\SelectionConfig\Types;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class ListOverlayConfig 
{
    private $adapter;
    private $listKey;
    private $displayProperties;
    private $icon;
    private $label;
    private $overlayTitle;
    private $_usedProperties = [];

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function adapter($value): static
    {
        $this->_usedProperties['adapter'] = true;
        $this->adapter = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function listKey($value): static
    {
        $this->_usedProperties['listKey'] = true;
        $this->listKey = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed> $value
     *
     * @return $this
     */
    public function displayProperties(ParamConfigurator|array $value): static
    {
        $this->_usedProperties['displayProperties'] = true;
        $this->displayProperties = $value;

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

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function label($value): static
    {
        $this->_usedProperties['label'] = true;
        $this->label = $value;

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
        if (array_key_exists('adapter', $config)) {
            $this->_usedProperties['adapter'] = true;
            $this->adapter = $config['adapter'];
            unset($config['adapter']);
        }

        if (array_key_exists('list_key', $config)) {
            $this->_usedProperties['listKey'] = true;
            $this->listKey = $config['list_key'];
            unset($config['list_key']);
        }

        if (array_key_exists('display_properties', $config)) {
            $this->_usedProperties['displayProperties'] = true;
            $this->displayProperties = $config['display_properties'];
            unset($config['display_properties']);
        }

        if (array_key_exists('icon', $config)) {
            $this->_usedProperties['icon'] = true;
            $this->icon = $config['icon'];
            unset($config['icon']);
        }

        if (array_key_exists('label', $config)) {
            $this->_usedProperties['label'] = true;
            $this->label = $config['label'];
            unset($config['label']);
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
        if (isset($this->_usedProperties['adapter'])) {
            $output['adapter'] = $this->adapter;
        }
        if (isset($this->_usedProperties['listKey'])) {
            $output['list_key'] = $this->listKey;
        }
        if (isset($this->_usedProperties['displayProperties'])) {
            $output['display_properties'] = $this->displayProperties;
        }
        if (isset($this->_usedProperties['icon'])) {
            $output['icon'] = $this->icon;
        }
        if (isset($this->_usedProperties['label'])) {
            $output['label'] = $this->label;
        }
        if (isset($this->_usedProperties['overlayTitle'])) {
            $output['overlay_title'] = $this->overlayTitle;
        }

        return $output;
    }

}
