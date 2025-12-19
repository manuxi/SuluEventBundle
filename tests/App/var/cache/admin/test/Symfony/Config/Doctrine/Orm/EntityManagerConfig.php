<?php

namespace Symfony\Config\Doctrine\Orm;

require_once __DIR__.\DIRECTORY_SEPARATOR.'EntityManagerConfig'.\DIRECTORY_SEPARATOR.'QueryCacheDriverConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'EntityManagerConfig'.\DIRECTORY_SEPARATOR.'MetadataCacheDriverConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'EntityManagerConfig'.\DIRECTORY_SEPARATOR.'ResultCacheDriverConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'EntityManagerConfig'.\DIRECTORY_SEPARATOR.'EntityListenersConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'EntityManagerConfig'.\DIRECTORY_SEPARATOR.'SecondLevelCacheConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'EntityManagerConfig'.\DIRECTORY_SEPARATOR.'MappingConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'EntityManagerConfig'.\DIRECTORY_SEPARATOR.'DqlConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'EntityManagerConfig'.\DIRECTORY_SEPARATOR.'FilterConfig.php';

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Loader\ParamConfigurator;

/**
 * This class is automatically generated to help in creating a config.
 */
class EntityManagerConfig 
{
    private $queryCacheDriver;
    private $metadataCacheDriver;
    private $resultCacheDriver;
    private $entityListeners;
    private $connection;
    private $classMetadataFactoryName;
    private $defaultRepositoryClass;
    private $autoMapping;
    private $namingStrategy;
    private $quoteStrategy;
    private $typedFieldMapper;
    private $entityListenerResolver;
    private $fetchModeSubselectBatchSize;
    private $repositoryFactory;
    private $schemaIgnoreClasses;
    private $reportFieldsWhereDeclared;
    private $validateXmlMapping;
    private $secondLevelCache;
    private $hydrators;
    private $mappings;
    private $dql;
    private $filters;
    private $identityGenerationPreferences;
    private $_usedProperties = [];

    /**
     * @template TValue of string|array
     * @param TValue $value
     * @default {"type":null}
     * @return \Symfony\Config\Doctrine\Orm\EntityManagerConfig\QueryCacheDriverConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\Doctrine\Orm\EntityManagerConfig\QueryCacheDriverConfig : static)
     */
    public function queryCacheDriver(string|array $value = []): \Symfony\Config\Doctrine\Orm\EntityManagerConfig\QueryCacheDriverConfig|static
    {
        if (!\is_array($value)) {
            $this->_usedProperties['queryCacheDriver'] = true;
            $this->queryCacheDriver = $value;

            return $this;
        }

        if (!$this->queryCacheDriver instanceof \Symfony\Config\Doctrine\Orm\EntityManagerConfig\QueryCacheDriverConfig) {
            $this->_usedProperties['queryCacheDriver'] = true;
            $this->queryCacheDriver = new \Symfony\Config\Doctrine\Orm\EntityManagerConfig\QueryCacheDriverConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "queryCacheDriver()" has already been initialized. You cannot pass values the second time you call queryCacheDriver().');
        }

        return $this->queryCacheDriver;
    }

    /**
     * @template TValue of string|array
     * @param TValue $value
     * @return \Symfony\Config\Doctrine\Orm\EntityManagerConfig\MetadataCacheDriverConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\Doctrine\Orm\EntityManagerConfig\MetadataCacheDriverConfig : static)
     */
    public function metadataCacheDriver(string|array $value = []): \Symfony\Config\Doctrine\Orm\EntityManagerConfig\MetadataCacheDriverConfig|static
    {
        if (!\is_array($value)) {
            $this->_usedProperties['metadataCacheDriver'] = true;
            $this->metadataCacheDriver = $value;

            return $this;
        }

        if (!$this->metadataCacheDriver instanceof \Symfony\Config\Doctrine\Orm\EntityManagerConfig\MetadataCacheDriverConfig) {
            $this->_usedProperties['metadataCacheDriver'] = true;
            $this->metadataCacheDriver = new \Symfony\Config\Doctrine\Orm\EntityManagerConfig\MetadataCacheDriverConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "metadataCacheDriver()" has already been initialized. You cannot pass values the second time you call metadataCacheDriver().');
        }

        return $this->metadataCacheDriver;
    }

    /**
     * @template TValue of string|array
     * @param TValue $value
     * @default {"type":null}
     * @return \Symfony\Config\Doctrine\Orm\EntityManagerConfig\ResultCacheDriverConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\Doctrine\Orm\EntityManagerConfig\ResultCacheDriverConfig : static)
     */
    public function resultCacheDriver(string|array $value = []): \Symfony\Config\Doctrine\Orm\EntityManagerConfig\ResultCacheDriverConfig|static
    {
        if (!\is_array($value)) {
            $this->_usedProperties['resultCacheDriver'] = true;
            $this->resultCacheDriver = $value;

            return $this;
        }

        if (!$this->resultCacheDriver instanceof \Symfony\Config\Doctrine\Orm\EntityManagerConfig\ResultCacheDriverConfig) {
            $this->_usedProperties['resultCacheDriver'] = true;
            $this->resultCacheDriver = new \Symfony\Config\Doctrine\Orm\EntityManagerConfig\ResultCacheDriverConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "resultCacheDriver()" has already been initialized. You cannot pass values the second time you call resultCacheDriver().');
        }

        return $this->resultCacheDriver;
    }

    /**
     * @template TValue of mixed
     * @param TValue $value
     * @return \Symfony\Config\Doctrine\Orm\EntityManagerConfig\EntityListenersConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\Doctrine\Orm\EntityManagerConfig\EntityListenersConfig : static)
     */
    public function entityListeners(mixed $value = []): \Symfony\Config\Doctrine\Orm\EntityManagerConfig\EntityListenersConfig|static
    {
        if (!\is_array($value)) {
            $this->_usedProperties['entityListeners'] = true;
            $this->entityListeners = $value;

            return $this;
        }

        if (!$this->entityListeners instanceof \Symfony\Config\Doctrine\Orm\EntityManagerConfig\EntityListenersConfig) {
            $this->_usedProperties['entityListeners'] = true;
            $this->entityListeners = new \Symfony\Config\Doctrine\Orm\EntityManagerConfig\EntityListenersConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "entityListeners()" has already been initialized. You cannot pass values the second time you call entityListeners().');
        }

        return $this->entityListeners;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function connection($value): static
    {
        $this->_usedProperties['connection'] = true;
        $this->connection = $value;

        return $this;
    }

    /**
     * @default 'Doctrine\\ORM\\Mapping\\ClassMetadataFactory'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function classMetadataFactoryName($value): static
    {
        $this->_usedProperties['classMetadataFactoryName'] = true;
        $this->classMetadataFactoryName = $value;

        return $this;
    }

    /**
     * @default 'Doctrine\\ORM\\EntityRepository'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function defaultRepositoryClass($value): static
    {
        $this->_usedProperties['defaultRepositoryClass'] = true;
        $this->defaultRepositoryClass = $value;

        return $this;
    }

    /**
     * @default false
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function autoMapping($value): static
    {
        $this->_usedProperties['autoMapping'] = true;
        $this->autoMapping = $value;

        return $this;
    }

    /**
     * @default 'doctrine.orm.naming_strategy.default'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function namingStrategy($value): static
    {
        $this->_usedProperties['namingStrategy'] = true;
        $this->namingStrategy = $value;

        return $this;
    }

    /**
     * @default 'doctrine.orm.quote_strategy.default'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function quoteStrategy($value): static
    {
        $this->_usedProperties['quoteStrategy'] = true;
        $this->quoteStrategy = $value;

        return $this;
    }

    /**
     * @default 'doctrine.orm.typed_field_mapper.default'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function typedFieldMapper($value): static
    {
        $this->_usedProperties['typedFieldMapper'] = true;
        $this->typedFieldMapper = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function entityListenerResolver($value): static
    {
        $this->_usedProperties['entityListenerResolver'] = true;
        $this->entityListenerResolver = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function fetchModeSubselectBatchSize($value): static
    {
        $this->_usedProperties['fetchModeSubselectBatchSize'] = true;
        $this->fetchModeSubselectBatchSize = $value;

        return $this;
    }

    /**
     * @default 'doctrine.orm.container_repository_factory'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function repositoryFactory($value): static
    {
        $this->_usedProperties['repositoryFactory'] = true;
        $this->repositoryFactory = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed> $value
     *
     * @return $this
     */
    public function schemaIgnoreClasses(ParamConfigurator|array $value): static
    {
        $this->_usedProperties['schemaIgnoreClasses'] = true;
        $this->schemaIgnoreClasses = $value;

        return $this;
    }

    /**
     * Set to "true" to opt-in to the new mapping driver mode that was added in Doctrine ORM 2.16 and will be mandatory in ORM 3.0. See https://github.com/doctrine/orm/pull/10455.
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function reportFieldsWhereDeclared($value): static
    {
        $this->_usedProperties['reportFieldsWhereDeclared'] = true;
        $this->reportFieldsWhereDeclared = $value;

        return $this;
    }

    /**
     * Set to "true" to opt-in to the new mapping driver mode that was added in Doctrine ORM 2.14. See https://github.com/doctrine/orm/pull/6728.
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function validateXmlMapping($value): static
    {
        $this->_usedProperties['validateXmlMapping'] = true;
        $this->validateXmlMapping = $value;

        return $this;
    }

    public function secondLevelCache(array $value = []): \Symfony\Config\Doctrine\Orm\EntityManagerConfig\SecondLevelCacheConfig
    {
        if (null === $this->secondLevelCache) {
            $this->_usedProperties['secondLevelCache'] = true;
            $this->secondLevelCache = new \Symfony\Config\Doctrine\Orm\EntityManagerConfig\SecondLevelCacheConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "secondLevelCache()" has already been initialized. You cannot pass values the second time you call secondLevelCache().');
        }

        return $this->secondLevelCache;
    }

    /**
     * @return $this
     */
    public function hydrator(string $name, mixed $value): static
    {
        $this->_usedProperties['hydrators'] = true;
        $this->hydrators[$name] = $value;

        return $this;
    }

    /**
     * @template TValue of string|bool|array
     * @param TValue $value
     * @return \Symfony\Config\Doctrine\Orm\EntityManagerConfig\MappingConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\Doctrine\Orm\EntityManagerConfig\MappingConfig : static)
     */
    public function mapping(string $name, string|bool|array $value = []): \Symfony\Config\Doctrine\Orm\EntityManagerConfig\MappingConfig|static
    {
        if (!\is_array($value)) {
            $this->_usedProperties['mappings'] = true;
            $this->mappings[$name] = $value;

            return $this;
        }

        if (!isset($this->mappings[$name]) || !$this->mappings[$name] instanceof \Symfony\Config\Doctrine\Orm\EntityManagerConfig\MappingConfig) {
            $this->_usedProperties['mappings'] = true;
            $this->mappings[$name] = new \Symfony\Config\Doctrine\Orm\EntityManagerConfig\MappingConfig($value);
        } elseif (1 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "mapping()" has already been initialized. You cannot pass values the second time you call mapping().');
        }

        return $this->mappings[$name];
    }

    public function dql(array $value = []): \Symfony\Config\Doctrine\Orm\EntityManagerConfig\DqlConfig
    {
        if (null === $this->dql) {
            $this->_usedProperties['dql'] = true;
            $this->dql = new \Symfony\Config\Doctrine\Orm\EntityManagerConfig\DqlConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "dql()" has already been initialized. You cannot pass values the second time you call dql().');
        }

        return $this->dql;
    }

    /**
     * @template TValue of mixed
     * @param TValue $value
     * Register SQL Filters in the entity manager
     * @return \Symfony\Config\Doctrine\Orm\EntityManagerConfig\FilterConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\Doctrine\Orm\EntityManagerConfig\FilterConfig : static)
     */
    public function filter(string $name, mixed $value = []): \Symfony\Config\Doctrine\Orm\EntityManagerConfig\FilterConfig|static
    {
        if (!\is_array($value)) {
            $this->_usedProperties['filters'] = true;
            $this->filters[$name] = $value;

            return $this;
        }

        if (!isset($this->filters[$name]) || !$this->filters[$name] instanceof \Symfony\Config\Doctrine\Orm\EntityManagerConfig\FilterConfig) {
            $this->_usedProperties['filters'] = true;
            $this->filters[$name] = new \Symfony\Config\Doctrine\Orm\EntityManagerConfig\FilterConfig($value);
        } elseif (1 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "filter()" has already been initialized. You cannot pass values the second time you call filter().');
        }

        return $this->filters[$name];
    }

    /**
     * @return $this
     */
    public function identityGenerationPreference(string $platform, mixed $value): static
    {
        $this->_usedProperties['identityGenerationPreferences'] = true;
        $this->identityGenerationPreferences[$platform] = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('query_cache_driver', $config)) {
            $this->_usedProperties['queryCacheDriver'] = true;
            $this->queryCacheDriver = \is_array($config['query_cache_driver']) ? new \Symfony\Config\Doctrine\Orm\EntityManagerConfig\QueryCacheDriverConfig($config['query_cache_driver']) : $config['query_cache_driver'];
            unset($config['query_cache_driver']);
        }

        if (array_key_exists('metadata_cache_driver', $config)) {
            $this->_usedProperties['metadataCacheDriver'] = true;
            $this->metadataCacheDriver = \is_array($config['metadata_cache_driver']) ? new \Symfony\Config\Doctrine\Orm\EntityManagerConfig\MetadataCacheDriverConfig($config['metadata_cache_driver']) : $config['metadata_cache_driver'];
            unset($config['metadata_cache_driver']);
        }

        if (array_key_exists('result_cache_driver', $config)) {
            $this->_usedProperties['resultCacheDriver'] = true;
            $this->resultCacheDriver = \is_array($config['result_cache_driver']) ? new \Symfony\Config\Doctrine\Orm\EntityManagerConfig\ResultCacheDriverConfig($config['result_cache_driver']) : $config['result_cache_driver'];
            unset($config['result_cache_driver']);
        }

        if (array_key_exists('entity_listeners', $config)) {
            $this->_usedProperties['entityListeners'] = true;
            $this->entityListeners = \is_array($config['entity_listeners']) ? new \Symfony\Config\Doctrine\Orm\EntityManagerConfig\EntityListenersConfig($config['entity_listeners']) : $config['entity_listeners'];
            unset($config['entity_listeners']);
        }

        if (array_key_exists('connection', $config)) {
            $this->_usedProperties['connection'] = true;
            $this->connection = $config['connection'];
            unset($config['connection']);
        }

        if (array_key_exists('class_metadata_factory_name', $config)) {
            $this->_usedProperties['classMetadataFactoryName'] = true;
            $this->classMetadataFactoryName = $config['class_metadata_factory_name'];
            unset($config['class_metadata_factory_name']);
        }

        if (array_key_exists('default_repository_class', $config)) {
            $this->_usedProperties['defaultRepositoryClass'] = true;
            $this->defaultRepositoryClass = $config['default_repository_class'];
            unset($config['default_repository_class']);
        }

        if (array_key_exists('auto_mapping', $config)) {
            $this->_usedProperties['autoMapping'] = true;
            $this->autoMapping = $config['auto_mapping'];
            unset($config['auto_mapping']);
        }

        if (array_key_exists('naming_strategy', $config)) {
            $this->_usedProperties['namingStrategy'] = true;
            $this->namingStrategy = $config['naming_strategy'];
            unset($config['naming_strategy']);
        }

        if (array_key_exists('quote_strategy', $config)) {
            $this->_usedProperties['quoteStrategy'] = true;
            $this->quoteStrategy = $config['quote_strategy'];
            unset($config['quote_strategy']);
        }

        if (array_key_exists('typed_field_mapper', $config)) {
            $this->_usedProperties['typedFieldMapper'] = true;
            $this->typedFieldMapper = $config['typed_field_mapper'];
            unset($config['typed_field_mapper']);
        }

        if (array_key_exists('entity_listener_resolver', $config)) {
            $this->_usedProperties['entityListenerResolver'] = true;
            $this->entityListenerResolver = $config['entity_listener_resolver'];
            unset($config['entity_listener_resolver']);
        }

        if (array_key_exists('fetch_mode_subselect_batch_size', $config)) {
            $this->_usedProperties['fetchModeSubselectBatchSize'] = true;
            $this->fetchModeSubselectBatchSize = $config['fetch_mode_subselect_batch_size'];
            unset($config['fetch_mode_subselect_batch_size']);
        }

        if (array_key_exists('repository_factory', $config)) {
            $this->_usedProperties['repositoryFactory'] = true;
            $this->repositoryFactory = $config['repository_factory'];
            unset($config['repository_factory']);
        }

        if (array_key_exists('schema_ignore_classes', $config)) {
            $this->_usedProperties['schemaIgnoreClasses'] = true;
            $this->schemaIgnoreClasses = $config['schema_ignore_classes'];
            unset($config['schema_ignore_classes']);
        }

        if (array_key_exists('report_fields_where_declared', $config)) {
            $this->_usedProperties['reportFieldsWhereDeclared'] = true;
            $this->reportFieldsWhereDeclared = $config['report_fields_where_declared'];
            unset($config['report_fields_where_declared']);
        }

        if (array_key_exists('validate_xml_mapping', $config)) {
            $this->_usedProperties['validateXmlMapping'] = true;
            $this->validateXmlMapping = $config['validate_xml_mapping'];
            unset($config['validate_xml_mapping']);
        }

        if (array_key_exists('second_level_cache', $config)) {
            $this->_usedProperties['secondLevelCache'] = true;
            $this->secondLevelCache = new \Symfony\Config\Doctrine\Orm\EntityManagerConfig\SecondLevelCacheConfig($config['second_level_cache']);
            unset($config['second_level_cache']);
        }

        if (array_key_exists('hydrators', $config)) {
            $this->_usedProperties['hydrators'] = true;
            $this->hydrators = $config['hydrators'];
            unset($config['hydrators']);
        }

        if (array_key_exists('mappings', $config)) {
            $this->_usedProperties['mappings'] = true;
            $this->mappings = array_map(fn ($v) => \is_array($v) ? new \Symfony\Config\Doctrine\Orm\EntityManagerConfig\MappingConfig($v) : $v, $config['mappings']);
            unset($config['mappings']);
        }

        if (array_key_exists('dql', $config)) {
            $this->_usedProperties['dql'] = true;
            $this->dql = new \Symfony\Config\Doctrine\Orm\EntityManagerConfig\DqlConfig($config['dql']);
            unset($config['dql']);
        }

        if (array_key_exists('filters', $config)) {
            $this->_usedProperties['filters'] = true;
            $this->filters = array_map(fn ($v) => \is_array($v) ? new \Symfony\Config\Doctrine\Orm\EntityManagerConfig\FilterConfig($v) : $v, $config['filters']);
            unset($config['filters']);
        }

        if (array_key_exists('identity_generation_preferences', $config)) {
            $this->_usedProperties['identityGenerationPreferences'] = true;
            $this->identityGenerationPreferences = $config['identity_generation_preferences'];
            unset($config['identity_generation_preferences']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['queryCacheDriver'])) {
            $output['query_cache_driver'] = $this->queryCacheDriver instanceof \Symfony\Config\Doctrine\Orm\EntityManagerConfig\QueryCacheDriverConfig ? $this->queryCacheDriver->toArray() : $this->queryCacheDriver;
        }
        if (isset($this->_usedProperties['metadataCacheDriver'])) {
            $output['metadata_cache_driver'] = $this->metadataCacheDriver instanceof \Symfony\Config\Doctrine\Orm\EntityManagerConfig\MetadataCacheDriverConfig ? $this->metadataCacheDriver->toArray() : $this->metadataCacheDriver;
        }
        if (isset($this->_usedProperties['resultCacheDriver'])) {
            $output['result_cache_driver'] = $this->resultCacheDriver instanceof \Symfony\Config\Doctrine\Orm\EntityManagerConfig\ResultCacheDriverConfig ? $this->resultCacheDriver->toArray() : $this->resultCacheDriver;
        }
        if (isset($this->_usedProperties['entityListeners'])) {
            $output['entity_listeners'] = $this->entityListeners instanceof \Symfony\Config\Doctrine\Orm\EntityManagerConfig\EntityListenersConfig ? $this->entityListeners->toArray() : $this->entityListeners;
        }
        if (isset($this->_usedProperties['connection'])) {
            $output['connection'] = $this->connection;
        }
        if (isset($this->_usedProperties['classMetadataFactoryName'])) {
            $output['class_metadata_factory_name'] = $this->classMetadataFactoryName;
        }
        if (isset($this->_usedProperties['defaultRepositoryClass'])) {
            $output['default_repository_class'] = $this->defaultRepositoryClass;
        }
        if (isset($this->_usedProperties['autoMapping'])) {
            $output['auto_mapping'] = $this->autoMapping;
        }
        if (isset($this->_usedProperties['namingStrategy'])) {
            $output['naming_strategy'] = $this->namingStrategy;
        }
        if (isset($this->_usedProperties['quoteStrategy'])) {
            $output['quote_strategy'] = $this->quoteStrategy;
        }
        if (isset($this->_usedProperties['typedFieldMapper'])) {
            $output['typed_field_mapper'] = $this->typedFieldMapper;
        }
        if (isset($this->_usedProperties['entityListenerResolver'])) {
            $output['entity_listener_resolver'] = $this->entityListenerResolver;
        }
        if (isset($this->_usedProperties['fetchModeSubselectBatchSize'])) {
            $output['fetch_mode_subselect_batch_size'] = $this->fetchModeSubselectBatchSize;
        }
        if (isset($this->_usedProperties['repositoryFactory'])) {
            $output['repository_factory'] = $this->repositoryFactory;
        }
        if (isset($this->_usedProperties['schemaIgnoreClasses'])) {
            $output['schema_ignore_classes'] = $this->schemaIgnoreClasses;
        }
        if (isset($this->_usedProperties['reportFieldsWhereDeclared'])) {
            $output['report_fields_where_declared'] = $this->reportFieldsWhereDeclared;
        }
        if (isset($this->_usedProperties['validateXmlMapping'])) {
            $output['validate_xml_mapping'] = $this->validateXmlMapping;
        }
        if (isset($this->_usedProperties['secondLevelCache'])) {
            $output['second_level_cache'] = $this->secondLevelCache->toArray();
        }
        if (isset($this->_usedProperties['hydrators'])) {
            $output['hydrators'] = $this->hydrators;
        }
        if (isset($this->_usedProperties['mappings'])) {
            $output['mappings'] = array_map(fn ($v) => $v instanceof \Symfony\Config\Doctrine\Orm\EntityManagerConfig\MappingConfig ? $v->toArray() : $v, $this->mappings);
        }
        if (isset($this->_usedProperties['dql'])) {
            $output['dql'] = $this->dql->toArray();
        }
        if (isset($this->_usedProperties['filters'])) {
            $output['filters'] = array_map(fn ($v) => $v instanceof \Symfony\Config\Doctrine\Orm\EntityManagerConfig\FilterConfig ? $v->toArray() : $v, $this->filters);
        }
        if (isset($this->_usedProperties['identityGenerationPreferences'])) {
            $output['identity_generation_preferences'] = $this->identityGenerationPreferences;
        }

        return $output;
    }

}
