<?php

namespace Symfony\Config\FosRest\BodyListener;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class ArrayNormalizerConfig 
{
    private $service;
    private $forms;
    private $_usedProperties = [];

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function service($value): static
    {
        $this->_usedProperties['service'] = true;
        $this->service = $value;

        return $this;
    }

    /**
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function forms($value): static
    {
        $this->_usedProperties['forms'] = true;
        $this->forms = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('service', $config)) {
            $this->_usedProperties['service'] = true;
            $this->service = $config['service'];
            unset($config['service']);
        }

        if (array_key_exists('forms', $config)) {
            $this->_usedProperties['forms'] = true;
            $this->forms = $config['forms'];
            unset($config['forms']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['service'])) {
            $output['service'] = $this->service;
        }
        if (isset($this->_usedProperties['forms'])) {
            $output['forms'] = $this->forms;
        }

        return $output;
    }

}
