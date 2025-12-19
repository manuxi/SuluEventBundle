<?php

namespace Symfony\Config\FosRest;

require_once __DIR__.\DIRECTORY_SEPARATOR.'FormatListener'.\DIRECTORY_SEPARATOR.'RuleConfig.php';

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class FormatListenerConfig 
{
    private $enabled;
    private $service;
    private $rules;
    private $_usedProperties = [];

    /**
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function enabled($value): static
    {
        $this->_usedProperties['enabled'] = true;
        $this->enabled = $value;

        return $this;
    }

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

    public function rule(array $value = []): \Symfony\Config\FosRest\FormatListener\RuleConfig
    {
        $this->_usedProperties['rules'] = true;

        return $this->rules[] = new \Symfony\Config\FosRest\FormatListener\RuleConfig($value);
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('enabled', $config)) {
            $this->_usedProperties['enabled'] = true;
            $this->enabled = $config['enabled'];
            unset($config['enabled']);
        }

        if (array_key_exists('service', $config)) {
            $this->_usedProperties['service'] = true;
            $this->service = $config['service'];
            unset($config['service']);
        }

        if (array_key_exists('rules', $config)) {
            $this->_usedProperties['rules'] = true;
            $this->rules = array_map(fn ($v) => new \Symfony\Config\FosRest\FormatListener\RuleConfig($v), $config['rules']);
            unset($config['rules']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['enabled'])) {
            $output['enabled'] = $this->enabled;
        }
        if (isset($this->_usedProperties['service'])) {
            $output['service'] = $this->service;
        }
        if (isset($this->_usedProperties['rules'])) {
            $output['rules'] = array_map(fn ($v) => $v->toArray(), $this->rules);
        }

        return $output;
    }

}
