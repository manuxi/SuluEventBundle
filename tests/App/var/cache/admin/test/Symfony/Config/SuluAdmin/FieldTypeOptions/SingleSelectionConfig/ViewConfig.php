<?php

namespace Symfony\Config\SuluAdmin\FieldTypeOptions\SingleSelectionConfig;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class ViewConfig 
{
    private $name;
    private $resultToView;
    private $_usedProperties = [];

    /**
     * @default null
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
     * @param ParamConfigurator|list<ParamConfigurator|mixed> $value
     *
     * @return $this
     */
    public function resultToView(ParamConfigurator|array $value): static
    {
        $this->_usedProperties['resultToView'] = true;
        $this->resultToView = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('name', $config)) {
            $this->_usedProperties['name'] = true;
            $this->name = $config['name'];
            unset($config['name']);
        }

        if (array_key_exists('result_to_view', $config)) {
            $this->_usedProperties['resultToView'] = true;
            $this->resultToView = $config['result_to_view'];
            unset($config['result_to_view']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['name'])) {
            $output['name'] = $this->name;
        }
        if (isset($this->_usedProperties['resultToView'])) {
            $output['result_to_view'] = $this->resultToView;
        }

        return $output;
    }

}
