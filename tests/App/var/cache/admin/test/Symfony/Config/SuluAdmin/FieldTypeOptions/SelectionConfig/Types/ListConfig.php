<?php

namespace Symfony\Config\SuluAdmin\FieldTypeOptions\SelectionConfig\Types;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class ListConfig 
{
    private $adapter;
    private $listKey;
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

        return $output;
    }

}
