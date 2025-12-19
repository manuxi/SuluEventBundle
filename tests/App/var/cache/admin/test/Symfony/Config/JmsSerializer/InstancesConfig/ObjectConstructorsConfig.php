<?php

namespace Symfony\Config\JmsSerializer\InstancesConfig;

require_once __DIR__.\DIRECTORY_SEPARATOR.'ObjectConstructors'.\DIRECTORY_SEPARATOR.'DoctrineConfig.php';

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class ObjectConstructorsConfig 
{
    private $doctrine;
    private $_usedProperties = [];

    /**
     * @template TValue of array|bool
     * @param TValue $value
     * @default {"enabled":true,"fallback_strategy":"null"}
     * @return \Symfony\Config\JmsSerializer\InstancesConfig\ObjectConstructors\DoctrineConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\JmsSerializer\InstancesConfig\ObjectConstructors\DoctrineConfig : static)
     */
    public function doctrine(array|bool $value = []): \Symfony\Config\JmsSerializer\InstancesConfig\ObjectConstructors\DoctrineConfig|static
    {
        if (!\is_array($value)) {
            $this->_usedProperties['doctrine'] = true;
            $this->doctrine = $value;

            return $this;
        }

        if (!$this->doctrine instanceof \Symfony\Config\JmsSerializer\InstancesConfig\ObjectConstructors\DoctrineConfig) {
            $this->_usedProperties['doctrine'] = true;
            $this->doctrine = new \Symfony\Config\JmsSerializer\InstancesConfig\ObjectConstructors\DoctrineConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "doctrine()" has already been initialized. You cannot pass values the second time you call doctrine().');
        }

        return $this->doctrine;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('doctrine', $config)) {
            $this->_usedProperties['doctrine'] = true;
            $this->doctrine = \is_array($config['doctrine']) ? new \Symfony\Config\JmsSerializer\InstancesConfig\ObjectConstructors\DoctrineConfig($config['doctrine']) : $config['doctrine'];
            unset($config['doctrine']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['doctrine'])) {
            $output['doctrine'] = $this->doctrine instanceof \Symfony\Config\JmsSerializer\InstancesConfig\ObjectConstructors\DoctrineConfig ? $this->doctrine->toArray() : $this->doctrine;
        }

        return $output;
    }

}
