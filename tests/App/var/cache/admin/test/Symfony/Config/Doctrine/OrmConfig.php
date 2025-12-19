<?php

namespace Symfony\Config\Doctrine;

require_once __DIR__.\DIRECTORY_SEPARATOR.'Orm'.\DIRECTORY_SEPARATOR.'ControllerResolverConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Orm'.\DIRECTORY_SEPARATOR.'EntityManagerConfig.php';

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class OrmConfig 
{
    private $defaultEntityManager;
    private $autoGenerateProxyClasses;
    private $enableLazyGhostObjects;
    private $enableNativeLazyObjects;
    private $proxyDir;
    private $proxyNamespace;
    private $controllerResolver;
    private $entityManagers;
    private $resolveTargetEntities;
    private $_usedProperties = [];

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function defaultEntityManager($value): static
    {
        $this->_usedProperties['defaultEntityManager'] = true;
        $this->defaultEntityManager = $value;

        return $this;
    }

    /**
     * Auto generate mode possible values are: "NEVER", "ALWAYS", "FILE_NOT_EXISTS", "EVAL", "FILE_NOT_EXISTS_OR_CHANGED", this option is ignored when the "enable_native_lazy_objects" option is true
     * @default false
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function autoGenerateProxyClasses($value): static
    {
        $this->_usedProperties['autoGenerateProxyClasses'] = true;
        $this->autoGenerateProxyClasses = $value;

        return $this;
    }

    /**
     * Enables the new implementation of proxies based on lazy ghosts instead of using the legacy implementation
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function enableLazyGhostObjects($value): static
    {
        $this->_usedProperties['enableLazyGhostObjects'] = true;
        $this->enableLazyGhostObjects = $value;

        return $this;
    }

    /**
     * Enables the new native implementation of PHP lazy objects instead of generated proxies
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function enableNativeLazyObjects($value): static
    {
        $this->_usedProperties['enableNativeLazyObjects'] = true;
        $this->enableNativeLazyObjects = $value;

        return $this;
    }

    /**
     * Configures the path where generated proxy classes are saved when using non-native lazy objects, this option is ignored when the "enable_native_lazy_objects" option is true
     * @default '%kernel.build_dir%/doctrine/orm/Proxies'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function proxyDir($value): static
    {
        $this->_usedProperties['proxyDir'] = true;
        $this->proxyDir = $value;

        return $this;
    }

    /**
     * Defines the root namespace for generated proxy classes when using non-native lazy objects, this option is ignored when the "enable_native_lazy_objects" option is true
     * @default 'Proxies'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function proxyNamespace($value): static
    {
        $this->_usedProperties['proxyNamespace'] = true;
        $this->proxyNamespace = $value;

        return $this;
    }

    /**
     * @template TValue of array|bool
     * @param TValue $value
     * @default {"enabled":true,"auto_mapping":null,"evict_cache":false}
     * @return \Symfony\Config\Doctrine\Orm\ControllerResolverConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\Doctrine\Orm\ControllerResolverConfig : static)
     */
    public function controllerResolver(array|bool $value = []): \Symfony\Config\Doctrine\Orm\ControllerResolverConfig|static
    {
        if (!\is_array($value)) {
            $this->_usedProperties['controllerResolver'] = true;
            $this->controllerResolver = $value;

            return $this;
        }

        if (!$this->controllerResolver instanceof \Symfony\Config\Doctrine\Orm\ControllerResolverConfig) {
            $this->_usedProperties['controllerResolver'] = true;
            $this->controllerResolver = new \Symfony\Config\Doctrine\Orm\ControllerResolverConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "controllerResolver()" has already been initialized. You cannot pass values the second time you call controllerResolver().');
        }

        return $this->controllerResolver;
    }

    public function entityManager(string $name, array $value = []): \Symfony\Config\Doctrine\Orm\EntityManagerConfig
    {
        if (!isset($this->entityManagers[$name])) {
            $this->_usedProperties['entityManagers'] = true;
            $this->entityManagers[$name] = new \Symfony\Config\Doctrine\Orm\EntityManagerConfig($value);
        } elseif (1 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "entityManager()" has already been initialized. You cannot pass values the second time you call entityManager().');
        }

        return $this->entityManagers[$name];
    }

    /**
     * @return $this
     */
    public function resolveTargetEntity(string $interface, mixed $value): static
    {
        $this->_usedProperties['resolveTargetEntities'] = true;
        $this->resolveTargetEntities[$interface] = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('default_entity_manager', $config)) {
            $this->_usedProperties['defaultEntityManager'] = true;
            $this->defaultEntityManager = $config['default_entity_manager'];
            unset($config['default_entity_manager']);
        }

        if (array_key_exists('auto_generate_proxy_classes', $config)) {
            $this->_usedProperties['autoGenerateProxyClasses'] = true;
            $this->autoGenerateProxyClasses = $config['auto_generate_proxy_classes'];
            unset($config['auto_generate_proxy_classes']);
        }

        if (array_key_exists('enable_lazy_ghost_objects', $config)) {
            $this->_usedProperties['enableLazyGhostObjects'] = true;
            $this->enableLazyGhostObjects = $config['enable_lazy_ghost_objects'];
            unset($config['enable_lazy_ghost_objects']);
        }

        if (array_key_exists('enable_native_lazy_objects', $config)) {
            $this->_usedProperties['enableNativeLazyObjects'] = true;
            $this->enableNativeLazyObjects = $config['enable_native_lazy_objects'];
            unset($config['enable_native_lazy_objects']);
        }

        if (array_key_exists('proxy_dir', $config)) {
            $this->_usedProperties['proxyDir'] = true;
            $this->proxyDir = $config['proxy_dir'];
            unset($config['proxy_dir']);
        }

        if (array_key_exists('proxy_namespace', $config)) {
            $this->_usedProperties['proxyNamespace'] = true;
            $this->proxyNamespace = $config['proxy_namespace'];
            unset($config['proxy_namespace']);
        }

        if (array_key_exists('controller_resolver', $config)) {
            $this->_usedProperties['controllerResolver'] = true;
            $this->controllerResolver = \is_array($config['controller_resolver']) ? new \Symfony\Config\Doctrine\Orm\ControllerResolverConfig($config['controller_resolver']) : $config['controller_resolver'];
            unset($config['controller_resolver']);
        }

        if (array_key_exists('entity_managers', $config)) {
            $this->_usedProperties['entityManagers'] = true;
            $this->entityManagers = array_map(fn ($v) => new \Symfony\Config\Doctrine\Orm\EntityManagerConfig($v), $config['entity_managers']);
            unset($config['entity_managers']);
        }

        if (array_key_exists('resolve_target_entities', $config)) {
            $this->_usedProperties['resolveTargetEntities'] = true;
            $this->resolveTargetEntities = $config['resolve_target_entities'];
            unset($config['resolve_target_entities']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['defaultEntityManager'])) {
            $output['default_entity_manager'] = $this->defaultEntityManager;
        }
        if (isset($this->_usedProperties['autoGenerateProxyClasses'])) {
            $output['auto_generate_proxy_classes'] = $this->autoGenerateProxyClasses;
        }
        if (isset($this->_usedProperties['enableLazyGhostObjects'])) {
            $output['enable_lazy_ghost_objects'] = $this->enableLazyGhostObjects;
        }
        if (isset($this->_usedProperties['enableNativeLazyObjects'])) {
            $output['enable_native_lazy_objects'] = $this->enableNativeLazyObjects;
        }
        if (isset($this->_usedProperties['proxyDir'])) {
            $output['proxy_dir'] = $this->proxyDir;
        }
        if (isset($this->_usedProperties['proxyNamespace'])) {
            $output['proxy_namespace'] = $this->proxyNamespace;
        }
        if (isset($this->_usedProperties['controllerResolver'])) {
            $output['controller_resolver'] = $this->controllerResolver instanceof \Symfony\Config\Doctrine\Orm\ControllerResolverConfig ? $this->controllerResolver->toArray() : $this->controllerResolver;
        }
        if (isset($this->_usedProperties['entityManagers'])) {
            $output['entity_managers'] = array_map(fn ($v) => $v->toArray(), $this->entityManagers);
        }
        if (isset($this->_usedProperties['resolveTargetEntities'])) {
            $output['resolve_target_entities'] = $this->resolveTargetEntities;
        }

        return $output;
    }

}
