<?php

namespace Symfony\Config\SuluCore;

require_once __DIR__.\DIRECTORY_SEPARATOR.'FieldsDefaults'.\DIRECTORY_SEPARATOR.'TranslationsConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'FieldsDefaults'.\DIRECTORY_SEPARATOR.'WidthsConfig.php';

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class FieldsDefaultsConfig 
{
    private $translations;
    private $widths;
    private $_usedProperties = [];

    /**
     * @default {"id":"public.id","title":"public.title","name":"public.name","created":"public.created","changed":"public.changed"}
     */
    public function translations(array $value = []): \Symfony\Config\SuluCore\FieldsDefaults\TranslationsConfig
    {
        if (null === $this->translations) {
            $this->_usedProperties['translations'] = true;
            $this->translations = new \Symfony\Config\SuluCore\FieldsDefaults\TranslationsConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "translations()" has already been initialized. You cannot pass values the second time you call translations().');
        }

        return $this->translations;
    }

    /**
     * @default {"id":"50px"}
     */
    public function widths(array $value = []): \Symfony\Config\SuluCore\FieldsDefaults\WidthsConfig
    {
        if (null === $this->widths) {
            $this->_usedProperties['widths'] = true;
            $this->widths = new \Symfony\Config\SuluCore\FieldsDefaults\WidthsConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "widths()" has already been initialized. You cannot pass values the second time you call widths().');
        }

        return $this->widths;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('translations', $config)) {
            $this->_usedProperties['translations'] = true;
            $this->translations = new \Symfony\Config\SuluCore\FieldsDefaults\TranslationsConfig($config['translations']);
            unset($config['translations']);
        }

        if (array_key_exists('widths', $config)) {
            $this->_usedProperties['widths'] = true;
            $this->widths = new \Symfony\Config\SuluCore\FieldsDefaults\WidthsConfig($config['widths']);
            unset($config['widths']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['translations'])) {
            $output['translations'] = $this->translations->toArray();
        }
        if (isset($this->_usedProperties['widths'])) {
            $output['widths'] = $this->widths->toArray();
        }

        return $output;
    }

}
