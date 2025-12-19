<?php

namespace Symfony\Config\SuluCore\FieldsDefaults;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class TranslationsConfig 
{
    private $id;
    private $title;
    private $name;
    private $created;
    private $changed;
    private $_usedProperties = [];

    /**
     * @default 'public.id'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function id($value): static
    {
        $this->_usedProperties['id'] = true;
        $this->id = $value;

        return $this;
    }

    /**
     * @default 'public.title'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function title($value): static
    {
        $this->_usedProperties['title'] = true;
        $this->title = $value;

        return $this;
    }

    /**
     * @default 'public.name'
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
     * @default 'public.created'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function created($value): static
    {
        $this->_usedProperties['created'] = true;
        $this->created = $value;

        return $this;
    }

    /**
     * @default 'public.changed'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function changed($value): static
    {
        $this->_usedProperties['changed'] = true;
        $this->changed = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('id', $config)) {
            $this->_usedProperties['id'] = true;
            $this->id = $config['id'];
            unset($config['id']);
        }

        if (array_key_exists('title', $config)) {
            $this->_usedProperties['title'] = true;
            $this->title = $config['title'];
            unset($config['title']);
        }

        if (array_key_exists('name', $config)) {
            $this->_usedProperties['name'] = true;
            $this->name = $config['name'];
            unset($config['name']);
        }

        if (array_key_exists('created', $config)) {
            $this->_usedProperties['created'] = true;
            $this->created = $config['created'];
            unset($config['created']);
        }

        if (array_key_exists('changed', $config)) {
            $this->_usedProperties['changed'] = true;
            $this->changed = $config['changed'];
            unset($config['changed']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['id'])) {
            $output['id'] = $this->id;
        }
        if (isset($this->_usedProperties['title'])) {
            $output['title'] = $this->title;
        }
        if (isset($this->_usedProperties['name'])) {
            $output['name'] = $this->name;
        }
        if (isset($this->_usedProperties['created'])) {
            $output['created'] = $this->created;
        }
        if (isset($this->_usedProperties['changed'])) {
            $output['changed'] = $this->changed;
        }

        return $output;
    }

}
