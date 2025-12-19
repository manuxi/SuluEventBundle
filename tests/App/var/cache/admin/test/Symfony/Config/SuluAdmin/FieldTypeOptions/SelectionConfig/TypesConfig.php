<?php

namespace Symfony\Config\SuluAdmin\FieldTypeOptions\SelectionConfig;

require_once __DIR__.\DIRECTORY_SEPARATOR.'Types'.\DIRECTORY_SEPARATOR.'AutoCompleteConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Types'.\DIRECTORY_SEPARATOR.'ListConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Types'.\DIRECTORY_SEPARATOR.'ListOverlayConfig.php';

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class TypesConfig 
{
    private $autoComplete;
    private $list;
    private $listOverlay;
    private $_usedProperties = [];

    public function autoComplete(array $value = []): \Symfony\Config\SuluAdmin\FieldTypeOptions\SelectionConfig\Types\AutoCompleteConfig
    {
        if (null === $this->autoComplete) {
            $this->_usedProperties['autoComplete'] = true;
            $this->autoComplete = new \Symfony\Config\SuluAdmin\FieldTypeOptions\SelectionConfig\Types\AutoCompleteConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "autoComplete()" has already been initialized. You cannot pass values the second time you call autoComplete().');
        }

        return $this->autoComplete;
    }

    public function list(array $value = []): \Symfony\Config\SuluAdmin\FieldTypeOptions\SelectionConfig\Types\ListConfig
    {
        if (null === $this->list) {
            $this->_usedProperties['list'] = true;
            $this->list = new \Symfony\Config\SuluAdmin\FieldTypeOptions\SelectionConfig\Types\ListConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "list()" has already been initialized. You cannot pass values the second time you call list().');
        }

        return $this->list;
    }

    public function listOverlay(array $value = []): \Symfony\Config\SuluAdmin\FieldTypeOptions\SelectionConfig\Types\ListOverlayConfig
    {
        if (null === $this->listOverlay) {
            $this->_usedProperties['listOverlay'] = true;
            $this->listOverlay = new \Symfony\Config\SuluAdmin\FieldTypeOptions\SelectionConfig\Types\ListOverlayConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "listOverlay()" has already been initialized. You cannot pass values the second time you call listOverlay().');
        }

        return $this->listOverlay;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('auto_complete', $config)) {
            $this->_usedProperties['autoComplete'] = true;
            $this->autoComplete = new \Symfony\Config\SuluAdmin\FieldTypeOptions\SelectionConfig\Types\AutoCompleteConfig($config['auto_complete']);
            unset($config['auto_complete']);
        }

        if (array_key_exists('list', $config)) {
            $this->_usedProperties['list'] = true;
            $this->list = new \Symfony\Config\SuluAdmin\FieldTypeOptions\SelectionConfig\Types\ListConfig($config['list']);
            unset($config['list']);
        }

        if (array_key_exists('list_overlay', $config)) {
            $this->_usedProperties['listOverlay'] = true;
            $this->listOverlay = new \Symfony\Config\SuluAdmin\FieldTypeOptions\SelectionConfig\Types\ListOverlayConfig($config['list_overlay']);
            unset($config['list_overlay']);
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
        if (isset($this->_usedProperties['list'])) {
            $output['list'] = $this->list->toArray();
        }
        if (isset($this->_usedProperties['listOverlay'])) {
            $output['list_overlay'] = $this->listOverlay->toArray();
        }

        return $output;
    }

}
