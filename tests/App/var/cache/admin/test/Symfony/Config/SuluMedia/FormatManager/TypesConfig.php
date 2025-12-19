<?php

namespace Symfony\Config\SuluMedia\FormatManager;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class TypesConfig 
{
    private $type;
    private $mimeTypes;
    private $_usedProperties = [];

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function type($value): static
    {
        $this->_usedProperties['type'] = true;
        $this->type = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed> $value
     *
     * @return $this
     */
    public function mimeTypes(ParamConfigurator|array $value): static
    {
        $this->_usedProperties['mimeTypes'] = true;
        $this->mimeTypes = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('type', $config)) {
            $this->_usedProperties['type'] = true;
            $this->type = $config['type'];
            unset($config['type']);
        }

        if (array_key_exists('mimeTypes', $config)) {
            $this->_usedProperties['mimeTypes'] = true;
            $this->mimeTypes = $config['mimeTypes'];
            unset($config['mimeTypes']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['type'])) {
            $output['type'] = $this->type;
        }
        if (isset($this->_usedProperties['mimeTypes'])) {
            $output['mimeTypes'] = $this->mimeTypes;
        }

        return $output;
    }

}
