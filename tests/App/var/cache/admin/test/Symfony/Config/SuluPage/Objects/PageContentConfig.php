<?php

namespace Symfony\Config\SuluPage\Objects;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class PageContentConfig 
{
    private $model;
    private $_usedProperties = [];

    /**
     * @default 'Sulu\\Page\\Domain\\Model\\PageDimensionContent'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function model($value): static
    {
        $this->_usedProperties['model'] = true;
        $this->model = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('model', $config)) {
            $this->_usedProperties['model'] = true;
            $this->model = $config['model'];
            unset($config['model']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['model'])) {
            $output['model'] = $this->model;
        }

        return $output;
    }

}
