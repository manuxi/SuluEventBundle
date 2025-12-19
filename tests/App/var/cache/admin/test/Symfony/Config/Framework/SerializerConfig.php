<?php

namespace Symfony\Config\Framework;

require_once __DIR__.\DIRECTORY_SEPARATOR.'Serializer'.\DIRECTORY_SEPARATOR.'MappingConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Serializer'.\DIRECTORY_SEPARATOR.'NamedSerializerConfig.php';

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class SerializerConfig 
{
    private $enabled;
    private $enableAttributes;
    private $nameConverter;
    private $circularReferenceHandler;
    private $maxDepthHandler;
    private $mapping;
    private $defaultContext;
    private $namedSerializers;
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
     * @default true
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function enableAttributes($value): static
    {
        $this->_usedProperties['enableAttributes'] = true;
        $this->enableAttributes = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function nameConverter($value): static
    {
        $this->_usedProperties['nameConverter'] = true;
        $this->nameConverter = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function circularReferenceHandler($value): static
    {
        $this->_usedProperties['circularReferenceHandler'] = true;
        $this->circularReferenceHandler = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function maxDepthHandler($value): static
    {
        $this->_usedProperties['maxDepthHandler'] = true;
        $this->maxDepthHandler = $value;

        return $this;
    }

    /**
     * @default {"paths":[]}
     */
    public function mapping(array $value = []): \Symfony\Config\Framework\Serializer\MappingConfig
    {
        if (null === $this->mapping) {
            $this->_usedProperties['mapping'] = true;
            $this->mapping = new \Symfony\Config\Framework\Serializer\MappingConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "mapping()" has already been initialized. You cannot pass values the second time you call mapping().');
        }

        return $this->mapping;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed> $value
     *
     * @return $this
     */
    public function defaultContext(ParamConfigurator|array $value): static
    {
        $this->_usedProperties['defaultContext'] = true;
        $this->defaultContext = $value;

        return $this;
    }

    public function namedSerializer(string $name, array $value = []): \Symfony\Config\Framework\Serializer\NamedSerializerConfig
    {
        if (!isset($this->namedSerializers[$name])) {
            $this->_usedProperties['namedSerializers'] = true;
            $this->namedSerializers[$name] = new \Symfony\Config\Framework\Serializer\NamedSerializerConfig($value);
        } elseif (1 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "namedSerializer()" has already been initialized. You cannot pass values the second time you call namedSerializer().');
        }

        return $this->namedSerializers[$name];
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('enabled', $config)) {
            $this->_usedProperties['enabled'] = true;
            $this->enabled = $config['enabled'];
            unset($config['enabled']);
        }

        if (array_key_exists('enable_attributes', $config)) {
            $this->_usedProperties['enableAttributes'] = true;
            $this->enableAttributes = $config['enable_attributes'];
            unset($config['enable_attributes']);
        }

        if (array_key_exists('name_converter', $config)) {
            $this->_usedProperties['nameConverter'] = true;
            $this->nameConverter = $config['name_converter'];
            unset($config['name_converter']);
        }

        if (array_key_exists('circular_reference_handler', $config)) {
            $this->_usedProperties['circularReferenceHandler'] = true;
            $this->circularReferenceHandler = $config['circular_reference_handler'];
            unset($config['circular_reference_handler']);
        }

        if (array_key_exists('max_depth_handler', $config)) {
            $this->_usedProperties['maxDepthHandler'] = true;
            $this->maxDepthHandler = $config['max_depth_handler'];
            unset($config['max_depth_handler']);
        }

        if (array_key_exists('mapping', $config)) {
            $this->_usedProperties['mapping'] = true;
            $this->mapping = new \Symfony\Config\Framework\Serializer\MappingConfig($config['mapping']);
            unset($config['mapping']);
        }

        if (array_key_exists('default_context', $config)) {
            $this->_usedProperties['defaultContext'] = true;
            $this->defaultContext = $config['default_context'];
            unset($config['default_context']);
        }

        if (array_key_exists('named_serializers', $config)) {
            $this->_usedProperties['namedSerializers'] = true;
            $this->namedSerializers = array_map(fn ($v) => new \Symfony\Config\Framework\Serializer\NamedSerializerConfig($v), $config['named_serializers']);
            unset($config['named_serializers']);
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
        if (isset($this->_usedProperties['enableAttributes'])) {
            $output['enable_attributes'] = $this->enableAttributes;
        }
        if (isset($this->_usedProperties['nameConverter'])) {
            $output['name_converter'] = $this->nameConverter;
        }
        if (isset($this->_usedProperties['circularReferenceHandler'])) {
            $output['circular_reference_handler'] = $this->circularReferenceHandler;
        }
        if (isset($this->_usedProperties['maxDepthHandler'])) {
            $output['max_depth_handler'] = $this->maxDepthHandler;
        }
        if (isset($this->_usedProperties['mapping'])) {
            $output['mapping'] = $this->mapping->toArray();
        }
        if (isset($this->_usedProperties['defaultContext'])) {
            $output['default_context'] = $this->defaultContext;
        }
        if (isset($this->_usedProperties['namedSerializers'])) {
            $output['named_serializers'] = array_map(fn ($v) => $v->toArray(), $this->namedSerializers);
        }

        return $output;
    }

}
