<?php

namespace Symfony\Config;

require_once __DIR__.\DIRECTORY_SEPARATOR.'SuluEvent'.\DIRECTORY_SEPARATOR.'RoutingConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'SuluEvent'.\DIRECTORY_SEPARATOR.'TypesConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'SuluEvent'.\DIRECTORY_SEPARATOR.'ObjectsConfig.php';

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Loader\ParamConfigurator;

/**
 * This class is automatically generated to help in creating a config.
 */
class SuluEventConfig implements \Symfony\Component\Config\Builder\ConfigBuilderInterface
{
    private $routing;
    private $types;
    private $defaultType;
    private $listDateFormat;
    private $objects;
    private $_usedProperties = [];
    private $_hasDeprecatedCalls = false;

    /**
     * @default {"route_schema":"\/{parent}\/{object.getTitle()}"}
     * @deprecated since Symfony 7.4
     */
    public function routing(array $value = []): \Symfony\Config\SuluEvent\RoutingConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (null === $this->routing) {
            $this->_usedProperties['routing'] = true;
            $this->routing = new \Symfony\Config\SuluEvent\RoutingConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "routing()" has already been initialized. You cannot pass values the second time you call routing().');
        }

        return $this->routing;
    }

    /**
     * @default {"default":{"name":"Default","color":"#cccccc"}}
     * @deprecated since Symfony 7.4
     */
    public function types(string $key, array $value = []): \Symfony\Config\SuluEvent\TypesConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (!isset($this->types[$key])) {
            $this->_usedProperties['types'] = true;
            $this->types[$key] = new \Symfony\Config\SuluEvent\TypesConfig($value);
        } elseif (1 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "types()" has already been initialized. You cannot pass values the second time you call types().');
        }

        return $this->types[$key];
    }

    /**
     * @default 'default'
     * @param ParamConfigurator|mixed $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function defaultType($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['defaultType'] = true;
        $this->defaultType = $value;

        return $this;
    }

    /**
     * Format for date display in list view: "default", "clock_format", "time_labels"
     * @default 'clock_format'
     * @param ParamConfigurator|mixed $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function listDateFormat($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['listDateFormat'] = true;
        $this->listDateFormat = $value;

        return $this;
    }

    /**
     * @default {"event":{"model":"Manuxi\\SuluEventBundle\\Entity\\Event","repository":"Manuxi\\SuluEventBundle\\Repository\\EventRepository"},"event_dimension_content":{"model":"Manuxi\\SuluEventBundle\\Entity\\EventDimensionContent","repository":"Manuxi\\SuluEventBundle\\Repository\\EventDimensionContentRepository"},"location":{"model":"Manuxi\\SuluEventBundle\\Entity\\Location","repository":"Manuxi\\SuluEventBundle\\Repository\\LocationRepository"}}
     * @deprecated since Symfony 7.4
     */
    public function objects(array $value = []): \Symfony\Config\SuluEvent\ObjectsConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (null === $this->objects) {
            $this->_usedProperties['objects'] = true;
            $this->objects = new \Symfony\Config\SuluEvent\ObjectsConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "objects()" has already been initialized. You cannot pass values the second time you call objects().');
        }

        return $this->objects;
    }

    public function getExtensionAlias(): string
    {
        return 'sulu_event';
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('routing', $config)) {
            $this->_usedProperties['routing'] = true;
            $this->routing = new \Symfony\Config\SuluEvent\RoutingConfig($config['routing']);
            unset($config['routing']);
        }

        if (array_key_exists('types', $config)) {
            $this->_usedProperties['types'] = true;
            $this->types = array_map(fn ($v) => new \Symfony\Config\SuluEvent\TypesConfig($v), $config['types']);
            unset($config['types']);
        }

        if (array_key_exists('default_type', $config)) {
            $this->_usedProperties['defaultType'] = true;
            $this->defaultType = $config['default_type'];
            unset($config['default_type']);
        }

        if (array_key_exists('list_date_format', $config)) {
            $this->_usedProperties['listDateFormat'] = true;
            $this->listDateFormat = $config['list_date_format'];
            unset($config['list_date_format']);
        }

        if (array_key_exists('objects', $config)) {
            $this->_usedProperties['objects'] = true;
            $this->objects = new \Symfony\Config\SuluEvent\ObjectsConfig($config['objects']);
            unset($config['objects']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['routing'])) {
            $output['routing'] = $this->routing->toArray();
        }
        if (isset($this->_usedProperties['types'])) {
            $output['types'] = array_map(fn ($v) => $v->toArray(), $this->types);
        }
        if (isset($this->_usedProperties['defaultType'])) {
            $output['default_type'] = $this->defaultType;
        }
        if (isset($this->_usedProperties['listDateFormat'])) {
            $output['list_date_format'] = $this->listDateFormat;
        }
        if (isset($this->_usedProperties['objects'])) {
            $output['objects'] = $this->objects->toArray();
        }
        if ($this->_hasDeprecatedCalls) {
            trigger_deprecation('symfony/config', '7.4', 'Calling any fluent method on "%s" is deprecated; pass the configuration to the constructor instead.', $this::class);
        }

        return $output;
    }

}
