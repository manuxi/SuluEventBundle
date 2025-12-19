<?php

namespace Symfony\Config\Framework\Messenger;

require_once __DIR__.\DIRECTORY_SEPARATOR.'Serializer'.\DIRECTORY_SEPARATOR.'SymfonySerializerConfig.php';

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class SerializerConfig 
{
    private $defaultSerializer;
    private $symfonySerializer;
    private $_usedProperties = [];

    /**
     * Service id to use as the default serializer for the transports.
     * @default 'messenger.transport.native_php_serializer'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function defaultSerializer($value): static
    {
        $this->_usedProperties['defaultSerializer'] = true;
        $this->defaultSerializer = $value;

        return $this;
    }

    /**
     * @default {"format":"json","context":[]}
     */
    public function symfonySerializer(array $value = []): \Symfony\Config\Framework\Messenger\Serializer\SymfonySerializerConfig
    {
        if (null === $this->symfonySerializer) {
            $this->_usedProperties['symfonySerializer'] = true;
            $this->symfonySerializer = new \Symfony\Config\Framework\Messenger\Serializer\SymfonySerializerConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "symfonySerializer()" has already been initialized. You cannot pass values the second time you call symfonySerializer().');
        }

        return $this->symfonySerializer;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('default_serializer', $config)) {
            $this->_usedProperties['defaultSerializer'] = true;
            $this->defaultSerializer = $config['default_serializer'];
            unset($config['default_serializer']);
        }

        if (array_key_exists('symfony_serializer', $config)) {
            $this->_usedProperties['symfonySerializer'] = true;
            $this->symfonySerializer = new \Symfony\Config\Framework\Messenger\Serializer\SymfonySerializerConfig($config['symfony_serializer']);
            unset($config['symfony_serializer']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['defaultSerializer'])) {
            $output['default_serializer'] = $this->defaultSerializer;
        }
        if (isset($this->_usedProperties['symfonySerializer'])) {
            $output['symfony_serializer'] = $this->symfonySerializer->toArray();
        }

        return $output;
    }

}
