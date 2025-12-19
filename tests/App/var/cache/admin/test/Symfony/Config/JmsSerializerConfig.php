<?php

namespace Symfony\Config;

require_once __DIR__.\DIRECTORY_SEPARATOR.'JmsSerializer'.\DIRECTORY_SEPARATOR.'HandlersConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'JmsSerializer'.\DIRECTORY_SEPARATOR.'SubscribersConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'JmsSerializer'.\DIRECTORY_SEPARATOR.'ObjectConstructorsConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'JmsSerializer'.\DIRECTORY_SEPARATOR.'PropertyNamingConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'JmsSerializer'.\DIRECTORY_SEPARATOR.'ExpressionEvaluatorConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'JmsSerializer'.\DIRECTORY_SEPARATOR.'MetadataConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'JmsSerializer'.\DIRECTORY_SEPARATOR.'VisitorsConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'JmsSerializer'.\DIRECTORY_SEPARATOR.'DefaultContextConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'JmsSerializer'.\DIRECTORY_SEPARATOR.'InstancesConfig.php';

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class JmsSerializerConfig implements \Symfony\Component\Config\Builder\ConfigBuilderInterface
{
    private $twigEnabled;
    private $profiler;
    private $enumSupport;
    private $defaultValuePropertyReaderSupport;
    private $handlers;
    private $subscribers;
    private $objectConstructors;
    private $propertyNaming;
    private $expressionEvaluator;
    private $metadata;
    private $visitors;
    private $defaultContext;
    private $instances;
    private $_usedProperties = [];
    private $_hasDeprecatedCalls = false;

    /**
     * @default 'default'
     * @param ParamConfigurator|mixed $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function twigEnabled($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['twigEnabled'] = true;
        $this->twigEnabled = $value;

        return $this;
    }

    /**
     * @default true
     * @param ParamConfigurator|mixed $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function profiler($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['profiler'] = true;
        $this->profiler = $value;

        return $this;
    }

    /**
     * @default false
     * @param ParamConfigurator|mixed $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function enumSupport($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['enumSupport'] = true;
        $this->enumSupport = $value;

        return $this;
    }

    /**
     * @default false
     * @param ParamConfigurator|mixed $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function defaultValuePropertyReaderSupport($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['defaultValuePropertyReaderSupport'] = true;
        $this->defaultValuePropertyReaderSupport = $value;

        return $this;
    }

    /**
     * @default {"datetime":{"default_format":"Y-m-d\\TH:i:sP","default_deserialization_formats":[],"default_timezone":"Europe\/Berlin","cdata":true},"array_collection":{"initialize_excluded":false},"symfony_uid":{"default_format":"canonical","cdata":true}}
     * @deprecated since Symfony 7.4
     */
    public function handlers(array $value = []): \Symfony\Config\JmsSerializer\HandlersConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (null === $this->handlers) {
            $this->_usedProperties['handlers'] = true;
            $this->handlers = new \Symfony\Config\JmsSerializer\HandlersConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "handlers()" has already been initialized. You cannot pass values the second time you call handlers().');
        }

        return $this->handlers;
    }

    /**
     * @default {"doctrine_proxy":{"initialize_excluded":false,"initialize_virtual_types":false}}
     * @deprecated since Symfony 7.4
     */
    public function subscribers(array $value = []): \Symfony\Config\JmsSerializer\SubscribersConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (null === $this->subscribers) {
            $this->_usedProperties['subscribers'] = true;
            $this->subscribers = new \Symfony\Config\JmsSerializer\SubscribersConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "subscribers()" has already been initialized. You cannot pass values the second time you call subscribers().');
        }

        return $this->subscribers;
    }

    /**
     * @default {"doctrine":{"enabled":true,"fallback_strategy":"null"}}
     * @deprecated since Symfony 7.4
     */
    public function objectConstructors(array $value = []): \Symfony\Config\JmsSerializer\ObjectConstructorsConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (null === $this->objectConstructors) {
            $this->_usedProperties['objectConstructors'] = true;
            $this->objectConstructors = new \Symfony\Config\JmsSerializer\ObjectConstructorsConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "objectConstructors()" has already been initialized. You cannot pass values the second time you call objectConstructors().');
        }

        return $this->objectConstructors;
    }

    /**
     * @template TValue of string|array
     * @param TValue $value
     * @default {"separator":"_","lower_case":true}
     * @return \Symfony\Config\JmsSerializer\PropertyNamingConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\JmsSerializer\PropertyNamingConfig : static)
     * @deprecated since Symfony 7.4
     */
    public function propertyNaming(string|array $value = []): \Symfony\Config\JmsSerializer\PropertyNamingConfig|static
    {
        $this->_hasDeprecatedCalls = true;
        if (!\is_array($value)) {
            $this->_usedProperties['propertyNaming'] = true;
            $this->propertyNaming = $value;

            return $this;
        }

        if (!$this->propertyNaming instanceof \Symfony\Config\JmsSerializer\PropertyNamingConfig) {
            $this->_usedProperties['propertyNaming'] = true;
            $this->propertyNaming = new \Symfony\Config\JmsSerializer\PropertyNamingConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "propertyNaming()" has already been initialized. You cannot pass values the second time you call propertyNaming().');
        }

        return $this->propertyNaming;
    }

    /**
     * @template TValue of string|array
     * @param TValue $value
     * @default {"id":"jms_serializer.expression_evaluator"}
     * @return \Symfony\Config\JmsSerializer\ExpressionEvaluatorConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\JmsSerializer\ExpressionEvaluatorConfig : static)
     * @deprecated since Symfony 7.4
     */
    public function expressionEvaluator(string|array $value = []): \Symfony\Config\JmsSerializer\ExpressionEvaluatorConfig|static
    {
        $this->_hasDeprecatedCalls = true;
        if (!\is_array($value)) {
            $this->_usedProperties['expressionEvaluator'] = true;
            $this->expressionEvaluator = $value;

            return $this;
        }

        if (!$this->expressionEvaluator instanceof \Symfony\Config\JmsSerializer\ExpressionEvaluatorConfig) {
            $this->_usedProperties['expressionEvaluator'] = true;
            $this->expressionEvaluator = new \Symfony\Config\JmsSerializer\ExpressionEvaluatorConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "expressionEvaluator()" has already been initialized. You cannot pass values the second time you call expressionEvaluator().');
        }

        return $this->expressionEvaluator;
    }

    /**
     * @default {"warmup":{"paths":{"included":[],"excluded":[]}},"cache":"file","debug":true,"file_cache":{"dir":null},"include_interfaces":false,"auto_detection":true,"infer_types_from_doc_block":false,"infer_types_from_doctrine_metadata":true,"directories":[]}
     * @deprecated since Symfony 7.4
     */
    public function metadata(array $value = []): \Symfony\Config\JmsSerializer\MetadataConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (null === $this->metadata) {
            $this->_usedProperties['metadata'] = true;
            $this->metadata = new \Symfony\Config\JmsSerializer\MetadataConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "metadata()" has already been initialized. You cannot pass values the second time you call metadata().');
        }

        return $this->metadata;
    }

    /**
     * @default {"json_serialization":{"options":1024},"json_deserialization":{"options":0,"strict":false},"xml_serialization":{"format_output":false,"default_root_ns":""},"xml_deserialization":{"doctype_whitelist":[],"external_entities":false,"options":0}}
     * @deprecated since Symfony 7.4
     */
    public function visitors(array $value = []): \Symfony\Config\JmsSerializer\VisitorsConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (null === $this->visitors) {
            $this->_usedProperties['visitors'] = true;
            $this->visitors = new \Symfony\Config\JmsSerializer\VisitorsConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "visitors()" has already been initialized. You cannot pass values the second time you call visitors().');
        }

        return $this->visitors;
    }

    /**
     * @default {"serialization":{"attributes":[],"groups":[]},"deserialization":{"attributes":[],"groups":[]}}
     * @deprecated since Symfony 7.4
     */
    public function defaultContext(array $value = []): \Symfony\Config\JmsSerializer\DefaultContextConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (null === $this->defaultContext) {
            $this->_usedProperties['defaultContext'] = true;
            $this->defaultContext = new \Symfony\Config\JmsSerializer\DefaultContextConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "defaultContext()" has already been initialized. You cannot pass values the second time you call defaultContext().');
        }

        return $this->defaultContext;
    }

    /**
     * @deprecated since Symfony 7.4
     */
    public function instances(string $name, array $value = []): \Symfony\Config\JmsSerializer\InstancesConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (!isset($this->instances[$name])) {
            $this->_usedProperties['instances'] = true;
            $this->instances[$name] = new \Symfony\Config\JmsSerializer\InstancesConfig($value);
        } elseif (1 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "instances()" has already been initialized. You cannot pass values the second time you call instances().');
        }

        return $this->instances[$name];
    }

    public function getExtensionAlias(): string
    {
        return 'jms_serializer';
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('twig_enabled', $config)) {
            $this->_usedProperties['twigEnabled'] = true;
            $this->twigEnabled = $config['twig_enabled'];
            unset($config['twig_enabled']);
        }

        if (array_key_exists('profiler', $config)) {
            $this->_usedProperties['profiler'] = true;
            $this->profiler = $config['profiler'];
            unset($config['profiler']);
        }

        if (array_key_exists('enum_support', $config)) {
            $this->_usedProperties['enumSupport'] = true;
            $this->enumSupport = $config['enum_support'];
            unset($config['enum_support']);
        }

        if (array_key_exists('default_value_property_reader_support', $config)) {
            $this->_usedProperties['defaultValuePropertyReaderSupport'] = true;
            $this->defaultValuePropertyReaderSupport = $config['default_value_property_reader_support'];
            unset($config['default_value_property_reader_support']);
        }

        if (array_key_exists('handlers', $config)) {
            $this->_usedProperties['handlers'] = true;
            $this->handlers = new \Symfony\Config\JmsSerializer\HandlersConfig($config['handlers']);
            unset($config['handlers']);
        }

        if (array_key_exists('subscribers', $config)) {
            $this->_usedProperties['subscribers'] = true;
            $this->subscribers = new \Symfony\Config\JmsSerializer\SubscribersConfig($config['subscribers']);
            unset($config['subscribers']);
        }

        if (array_key_exists('object_constructors', $config)) {
            $this->_usedProperties['objectConstructors'] = true;
            $this->objectConstructors = new \Symfony\Config\JmsSerializer\ObjectConstructorsConfig($config['object_constructors']);
            unset($config['object_constructors']);
        }

        if (array_key_exists('property_naming', $config)) {
            $this->_usedProperties['propertyNaming'] = true;
            $this->propertyNaming = \is_array($config['property_naming']) ? new \Symfony\Config\JmsSerializer\PropertyNamingConfig($config['property_naming']) : $config['property_naming'];
            unset($config['property_naming']);
        }

        if (array_key_exists('expression_evaluator', $config)) {
            $this->_usedProperties['expressionEvaluator'] = true;
            $this->expressionEvaluator = \is_array($config['expression_evaluator']) ? new \Symfony\Config\JmsSerializer\ExpressionEvaluatorConfig($config['expression_evaluator']) : $config['expression_evaluator'];
            unset($config['expression_evaluator']);
        }

        if (array_key_exists('metadata', $config)) {
            $this->_usedProperties['metadata'] = true;
            $this->metadata = new \Symfony\Config\JmsSerializer\MetadataConfig($config['metadata']);
            unset($config['metadata']);
        }

        if (array_key_exists('visitors', $config)) {
            $this->_usedProperties['visitors'] = true;
            $this->visitors = new \Symfony\Config\JmsSerializer\VisitorsConfig($config['visitors']);
            unset($config['visitors']);
        }

        if (array_key_exists('default_context', $config)) {
            $this->_usedProperties['defaultContext'] = true;
            $this->defaultContext = new \Symfony\Config\JmsSerializer\DefaultContextConfig($config['default_context']);
            unset($config['default_context']);
        }

        if (array_key_exists('instances', $config)) {
            $this->_usedProperties['instances'] = true;
            $this->instances = array_map(fn ($v) => new \Symfony\Config\JmsSerializer\InstancesConfig($v), $config['instances']);
            unset($config['instances']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['twigEnabled'])) {
            $output['twig_enabled'] = $this->twigEnabled;
        }
        if (isset($this->_usedProperties['profiler'])) {
            $output['profiler'] = $this->profiler;
        }
        if (isset($this->_usedProperties['enumSupport'])) {
            $output['enum_support'] = $this->enumSupport;
        }
        if (isset($this->_usedProperties['defaultValuePropertyReaderSupport'])) {
            $output['default_value_property_reader_support'] = $this->defaultValuePropertyReaderSupport;
        }
        if (isset($this->_usedProperties['handlers'])) {
            $output['handlers'] = $this->handlers->toArray();
        }
        if (isset($this->_usedProperties['subscribers'])) {
            $output['subscribers'] = $this->subscribers->toArray();
        }
        if (isset($this->_usedProperties['objectConstructors'])) {
            $output['object_constructors'] = $this->objectConstructors->toArray();
        }
        if (isset($this->_usedProperties['propertyNaming'])) {
            $output['property_naming'] = $this->propertyNaming instanceof \Symfony\Config\JmsSerializer\PropertyNamingConfig ? $this->propertyNaming->toArray() : $this->propertyNaming;
        }
        if (isset($this->_usedProperties['expressionEvaluator'])) {
            $output['expression_evaluator'] = $this->expressionEvaluator instanceof \Symfony\Config\JmsSerializer\ExpressionEvaluatorConfig ? $this->expressionEvaluator->toArray() : $this->expressionEvaluator;
        }
        if (isset($this->_usedProperties['metadata'])) {
            $output['metadata'] = $this->metadata->toArray();
        }
        if (isset($this->_usedProperties['visitors'])) {
            $output['visitors'] = $this->visitors->toArray();
        }
        if (isset($this->_usedProperties['defaultContext'])) {
            $output['default_context'] = $this->defaultContext->toArray();
        }
        if (isset($this->_usedProperties['instances'])) {
            $output['instances'] = array_map(fn ($v) => $v->toArray(), $this->instances);
        }
        if ($this->_hasDeprecatedCalls) {
            trigger_deprecation('symfony/config', '7.4', 'Calling any fluent method on "%s" is deprecated; pass the configuration to the constructor instead.', $this::class);
        }

        return $output;
    }

}
