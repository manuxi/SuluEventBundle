<?php

namespace Symfony\Config\SuluAdmin\FieldTypeOptions\SingleSelectionConfig;

require_once __DIR__.\DIRECTORY_SEPARATOR.'Types'.\DIRECTORY_SEPARATOR.'AutoCompleteConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Types'.\DIRECTORY_SEPARATOR.'ListOverlayConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Types'.\DIRECTORY_SEPARATOR.'SingleSelectConfig.php';

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class TypesConfig 
{
    private $autoComplete;
    private $listOverlay;
    private $singleSelect;
    private $_usedProperties = [];

    public function autoComplete(array $value = []): \Symfony\Config\SuluAdmin\FieldTypeOptions\SingleSelectionConfig\Types\AutoCompleteConfig
    {
        if (null === $this->autoComplete) {
            $this->_usedProperties['autoComplete'] = true;
            $this->autoComplete = new \Symfony\Config\SuluAdmin\FieldTypeOptions\SingleSelectionConfig\Types\AutoCompleteConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "autoComplete()" has already been initialized. You cannot pass values the second time you call autoComplete().');
        }

        return $this->autoComplete;
    }

    public function listOverlay(array $value = []): \Symfony\Config\SuluAdmin\FieldTypeOptions\SingleSelectionConfig\Types\ListOverlayConfig
    {
        if (null === $this->listOverlay) {
            $this->_usedProperties['listOverlay'] = true;
            $this->listOverlay = new \Symfony\Config\SuluAdmin\FieldTypeOptions\SingleSelectionConfig\Types\ListOverlayConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "listOverlay()" has already been initialized. You cannot pass values the second time you call listOverlay().');
        }

        return $this->listOverlay;
    }

    public function singleSelect(array $value = []): \Symfony\Config\SuluAdmin\FieldTypeOptions\SingleSelectionConfig\Types\SingleSelectConfig
    {
        if (null === $this->singleSelect) {
            $this->_usedProperties['singleSelect'] = true;
            $this->singleSelect = new \Symfony\Config\SuluAdmin\FieldTypeOptions\SingleSelectionConfig\Types\SingleSelectConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "singleSelect()" has already been initialized. You cannot pass values the second time you call singleSelect().');
        }

        return $this->singleSelect;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('auto_complete', $config)) {
            $this->_usedProperties['autoComplete'] = true;
            $this->autoComplete = new \Symfony\Config\SuluAdmin\FieldTypeOptions\SingleSelectionConfig\Types\AutoCompleteConfig($config['auto_complete']);
            unset($config['auto_complete']);
        }

        if (array_key_exists('list_overlay', $config)) {
            $this->_usedProperties['listOverlay'] = true;
            $this->listOverlay = new \Symfony\Config\SuluAdmin\FieldTypeOptions\SingleSelectionConfig\Types\ListOverlayConfig($config['list_overlay']);
            unset($config['list_overlay']);
        }

        if (array_key_exists('single_select', $config)) {
            $this->_usedProperties['singleSelect'] = true;
            $this->singleSelect = new \Symfony\Config\SuluAdmin\FieldTypeOptions\SingleSelectionConfig\Types\SingleSelectConfig($config['single_select']);
            unset($config['single_select']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['autoComplete'])) {
            $output['auto_complete'] = $this->autoComplete->toArray();
        }
        if (isset($this->_usedProperties['listOverlay'])) {
            $output['list_overlay'] = $this->listOverlay->toArray();
        }
        if (isset($this->_usedProperties['singleSelect'])) {
            $output['single_select'] = $this->singleSelect->toArray();
        }

        return $output;
    }

}
