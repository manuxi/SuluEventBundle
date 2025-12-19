<?php

namespace Symfony\Config\Framework;

require_once __DIR__.\DIRECTORY_SEPARATOR.'Assets'.\DIRECTORY_SEPARATOR.'PackageConfig.php';

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class AssetsConfig 
{
    private $enabled;
    private $strictMode;
    private $versionStrategy;
    private $version;
    private $versionFormat;
    private $jsonManifestPath;
    private $basePath;
    private $baseUrls;
    private $packages;
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
     * Throw an exception if an entry is missing from the manifest.json.
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function strictMode($value): static
    {
        $this->_usedProperties['strictMode'] = true;
        $this->strictMode = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function versionStrategy($value): static
    {
        $this->_usedProperties['versionStrategy'] = true;
        $this->versionStrategy = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function version($value): static
    {
        $this->_usedProperties['version'] = true;
        $this->version = $value;

        return $this;
    }

    /**
     * @default '%%s?%%s'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function versionFormat($value): static
    {
        $this->_usedProperties['versionFormat'] = true;
        $this->versionFormat = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function jsonManifestPath($value): static
    {
        $this->_usedProperties['jsonManifestPath'] = true;
        $this->jsonManifestPath = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function basePath($value): static
    {
        $this->_usedProperties['basePath'] = true;
        $this->basePath = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed>|mixed $value
     *
     * @return $this
     */
    public function baseUrls(mixed $value): static
    {
        $this->_usedProperties['baseUrls'] = true;
        $this->baseUrls = $value;

        return $this;
    }

    public function package(string $name, array $value = []): \Symfony\Config\Framework\Assets\PackageConfig
    {
        if (!isset($this->packages[$name])) {
            $this->_usedProperties['packages'] = true;
            $this->packages[$name] = new \Symfony\Config\Framework\Assets\PackageConfig($value);
        } elseif (1 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "package()" has already been initialized. You cannot pass values the second time you call package().');
        }

        return $this->packages[$name];
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('enabled', $config)) {
            $this->_usedProperties['enabled'] = true;
            $this->enabled = $config['enabled'];
            unset($config['enabled']);
        }

        if (array_key_exists('strict_mode', $config)) {
            $this->_usedProperties['strictMode'] = true;
            $this->strictMode = $config['strict_mode'];
            unset($config['strict_mode']);
        }

        if (array_key_exists('version_strategy', $config)) {
            $this->_usedProperties['versionStrategy'] = true;
            $this->versionStrategy = $config['version_strategy'];
            unset($config['version_strategy']);
        }

        if (array_key_exists('version', $config)) {
            $this->_usedProperties['version'] = true;
            $this->version = $config['version'];
            unset($config['version']);
        }

        if (array_key_exists('version_format', $config)) {
            $this->_usedProperties['versionFormat'] = true;
            $this->versionFormat = $config['version_format'];
            unset($config['version_format']);
        }

        if (array_key_exists('json_manifest_path', $config)) {
            $this->_usedProperties['jsonManifestPath'] = true;
            $this->jsonManifestPath = $config['json_manifest_path'];
            unset($config['json_manifest_path']);
        }

        if (array_key_exists('base_path', $config)) {
            $this->_usedProperties['basePath'] = true;
            $this->basePath = $config['base_path'];
            unset($config['base_path']);
        }

        if (array_key_exists('base_urls', $config)) {
            $this->_usedProperties['baseUrls'] = true;
            $this->baseUrls = $config['base_urls'];
            unset($config['base_urls']);
        }

        if (array_key_exists('packages', $config)) {
            $this->_usedProperties['packages'] = true;
            $this->packages = array_map(fn ($v) => new \Symfony\Config\Framework\Assets\PackageConfig($v), $config['packages']);
            unset($config['packages']);
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
        if (isset($this->_usedProperties['strictMode'])) {
            $output['strict_mode'] = $this->strictMode;
        }
        if (isset($this->_usedProperties['versionStrategy'])) {
            $output['version_strategy'] = $this->versionStrategy;
        }
        if (isset($this->_usedProperties['version'])) {
            $output['version'] = $this->version;
        }
        if (isset($this->_usedProperties['versionFormat'])) {
            $output['version_format'] = $this->versionFormat;
        }
        if (isset($this->_usedProperties['jsonManifestPath'])) {
            $output['json_manifest_path'] = $this->jsonManifestPath;
        }
        if (isset($this->_usedProperties['basePath'])) {
            $output['base_path'] = $this->basePath;
        }
        if (isset($this->_usedProperties['baseUrls'])) {
            $output['base_urls'] = $this->baseUrls;
        }
        if (isset($this->_usedProperties['packages'])) {
            $output['packages'] = array_map(fn ($v) => $v->toArray(), $this->packages);
        }

        return $output;
    }

}
