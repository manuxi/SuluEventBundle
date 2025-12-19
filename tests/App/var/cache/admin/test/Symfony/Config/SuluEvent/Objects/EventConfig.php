<?php

namespace Symfony\Config\SuluEvent\Objects;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class EventConfig 
{
    private $model;
    private $repository;
    private $_usedProperties = [];

    /**
     * @default 'Manuxi\\SuluEventBundle\\Entity\\Event'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function model($value): static
    {
        $this->_usedProperties['model'] = true;
        $this->model = $value;

        return $this;
    }

    /**
     * @default 'Manuxi\\SuluEventBundle\\Repository\\EventRepository'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function repository($value): static
    {
        $this->_usedProperties['repository'] = true;
        $this->repository = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('model', $config)) {
            $this->_usedProperties['model'] = true;
            $this->model = $config['model'];
            unset($config['model']);
        }

        if (array_key_exists('repository', $config)) {
            $this->_usedProperties['repository'] = true;
            $this->repository = $config['repository'];
            unset($config['repository']);
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
        if (isset($this->_usedProperties['repository'])) {
            $output['repository'] = $this->repository;
        }

        return $output;
    }

}
