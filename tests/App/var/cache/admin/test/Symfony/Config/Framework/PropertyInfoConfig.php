<?php

namespace Symfony\Config\Framework;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class PropertyInfoConfig 
{
    private $enabled;
    private $withConstructorExtractor;
    private $_usedProperties = [];

    /**
     * @default true
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
     * Registers the constructor extractor.
     * @default null
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function withConstructorExtractor($value): static
    {
        $this->_usedProperties['withConstructorExtractor'] = true;
        $this->withConstructorExtractor = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('enabled', $config)) {
            $this->_usedProperties['enabled'] = true;
            $this->enabled = $config['enabled'];
            unset($config['enabled']);
        }

        if (array_key_exists('with_constructor_extractor', $config)) {
            $this->_usedProperties['withConstructorExtractor'] = true;
            $this->withConstructorExtractor = $config['with_constructor_extractor'];
            unset($config['with_constructor_extractor']);
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
        if (isset($this->_usedProperties['withConstructorExtractor'])) {
            $output['with_constructor_extractor'] = $this->withConstructorExtractor;
        }

        return $output;
    }

}
