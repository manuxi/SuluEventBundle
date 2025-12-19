<?php

namespace Symfony\Config\Framework\Serializer;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class MappingConfig 
{
    private $paths;
    private $_usedProperties = [];

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed> $value
     *
     * @return $this
     */
    public function paths(ParamConfigurator|array $value): static
    {
        $this->_usedProperties['paths'] = true;
        $this->paths = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('paths', $config)) {
            $this->_usedProperties['paths'] = true;
            $this->paths = $config['paths'];
            unset($config['paths']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['paths'])) {
            $output['paths'] = $this->paths;
        }

        return $output;
    }

}
