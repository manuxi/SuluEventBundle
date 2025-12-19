<?php

namespace Symfony\Config;

require_once __DIR__.\DIRECTORY_SEPARATOR.'CmsigSeal'.\DIRECTORY_SEPARATOR.'SchemasConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'CmsigSeal'.\DIRECTORY_SEPARATOR.'EnginesConfig.php';

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class CmsigSealConfig implements \Symfony\Component\Config\Builder\ConfigBuilderInterface
{
    private $indexNamePrefix;
    private $schemas;
    private $engines;
    private $_usedProperties = [];
    private $_hasDeprecatedCalls = false;

    /**
     * @param ParamConfigurator|mixed $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function indexNamePrefix($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['indexNamePrefix'] = true;
        $this->indexNamePrefix = $value;

        return $this;
    }

    /**
     * @deprecated since Symfony 7.4
     */
    public function schemas(string $name, array $value = []): \Symfony\Config\CmsigSeal\SchemasConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (!isset($this->schemas[$name])) {
            $this->_usedProperties['schemas'] = true;
            $this->schemas[$name] = new \Symfony\Config\CmsigSeal\SchemasConfig($value);
        } elseif (1 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "schemas()" has already been initialized. You cannot pass values the second time you call schemas().');
        }

        return $this->schemas[$name];
    }

    /**
     * @deprecated since Symfony 7.4
     */
    public function engines(string $name, array $value = []): \Symfony\Config\CmsigSeal\EnginesConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (!isset($this->engines[$name])) {
            $this->_usedProperties['engines'] = true;
            $this->engines[$name] = new \Symfony\Config\CmsigSeal\EnginesConfig($value);
        } elseif (1 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "engines()" has already been initialized. You cannot pass values the second time you call engines().');
        }

        return $this->engines[$name];
    }

    public function getExtensionAlias(): string
    {
        return 'cmsig_seal';
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('index_name_prefix', $config)) {
            $this->_usedProperties['indexNamePrefix'] = true;
            $this->indexNamePrefix = $config['index_name_prefix'];
            unset($config['index_name_prefix']);
        }

        if (array_key_exists('schemas', $config)) {
            $this->_usedProperties['schemas'] = true;
            $this->schemas = array_map(fn ($v) => new \Symfony\Config\CmsigSeal\SchemasConfig($v), $config['schemas']);
            unset($config['schemas']);
        }

        if (array_key_exists('engines', $config)) {
            $this->_usedProperties['engines'] = true;
            $this->engines = array_map(fn ($v) => new \Symfony\Config\CmsigSeal\EnginesConfig($v), $config['engines']);
            unset($config['engines']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['indexNamePrefix'])) {
            $output['index_name_prefix'] = $this->indexNamePrefix;
        }
        if (isset($this->_usedProperties['schemas'])) {
            $output['schemas'] = array_map(fn ($v) => $v->toArray(), $this->schemas);
        }
        if (isset($this->_usedProperties['engines'])) {
            $output['engines'] = array_map(fn ($v) => $v->toArray(), $this->engines);
        }
        if ($this->_hasDeprecatedCalls) {
            trigger_deprecation('symfony/config', '7.4', 'Calling any fluent method on "%s" is deprecated; pass the configuration to the constructor instead.', $this::class);
        }

        return $output;
    }

}
