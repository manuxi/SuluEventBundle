<?php

namespace Symfony\Config\Doctrine\Orm\EntityManagerConfig;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class DqlConfig 
{
    private $stringFunctions;
    private $numericFunctions;
    private $datetimeFunctions;
    private $_usedProperties = [];

    /**
     * @return $this
     */
    public function stringFunction(string $name, mixed $value): static
    {
        $this->_usedProperties['stringFunctions'] = true;
        $this->stringFunctions[$name] = $value;

        return $this;
    }

    /**
     * @return $this
     */
    public function numericFunction(string $name, mixed $value): static
    {
        $this->_usedProperties['numericFunctions'] = true;
        $this->numericFunctions[$name] = $value;

        return $this;
    }

    /**
     * @return $this
     */
    public function datetimeFunction(string $name, mixed $value): static
    {
        $this->_usedProperties['datetimeFunctions'] = true;
        $this->datetimeFunctions[$name] = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('string_functions', $config)) {
            $this->_usedProperties['stringFunctions'] = true;
            $this->stringFunctions = $config['string_functions'];
            unset($config['string_functions']);
        }

        if (array_key_exists('numeric_functions', $config)) {
            $this->_usedProperties['numericFunctions'] = true;
            $this->numericFunctions = $config['numeric_functions'];
            unset($config['numeric_functions']);
        }

        if (array_key_exists('datetime_functions', $config)) {
            $this->_usedProperties['datetimeFunctions'] = true;
            $this->datetimeFunctions = $config['datetime_functions'];
            unset($config['datetime_functions']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['stringFunctions'])) {
            $output['string_functions'] = $this->stringFunctions;
        }
        if (isset($this->_usedProperties['numericFunctions'])) {
            $output['numeric_functions'] = $this->numericFunctions;
        }
        if (isset($this->_usedProperties['datetimeFunctions'])) {
            $output['datetime_functions'] = $this->datetimeFunctions;
        }

        return $output;
    }

}
