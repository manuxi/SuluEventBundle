<?php

namespace Symfony\Config\Framework\Messenger\Serializer;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class SymfonySerializerConfig 
{
    private $format;
    private $context;
    private $_usedProperties = [];

    /**
     * Serialization format for the messenger.transport.symfony_serializer service (which is not the serializer used by default).
     * @default 'json'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function format($value): static
    {
        $this->_usedProperties['format'] = true;
        $this->format = $value;

        return $this;
    }

    /**
     * @return $this
     */
    public function context(string $name, mixed $value): static
    {
        $this->_usedProperties['context'] = true;
        $this->context[$name] = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('format', $config)) {
            $this->_usedProperties['format'] = true;
            $this->format = $config['format'];
            unset($config['format']);
        }

        if (array_key_exists('context', $config)) {
            $this->_usedProperties['context'] = true;
            $this->context = $config['context'];
            unset($config['context']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['format'])) {
            $output['format'] = $this->format;
        }
        if (isset($this->_usedProperties['context'])) {
            $output['context'] = $this->context;
        }

        return $output;
    }

}
