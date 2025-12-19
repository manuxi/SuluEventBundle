<?php

namespace Symfony\Config;

require_once __DIR__.\DIRECTORY_SEPARATOR.'Flysystem'.\DIRECTORY_SEPARATOR.'StorageConfig.php';

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class FlysystemConfig implements \Symfony\Component\Config\Builder\ConfigBuilderInterface
{
    private $storages;
    private $_usedProperties = [];
    private $_hasDeprecatedCalls = false;

    /**
     * @deprecated since Symfony 7.4
     */
    public function storage(string $name, array $value = []): \Symfony\Config\Flysystem\StorageConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (!isset($this->storages[$name])) {
            $this->_usedProperties['storages'] = true;
            $this->storages[$name] = new \Symfony\Config\Flysystem\StorageConfig($value);
        } elseif (1 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "storage()" has already been initialized. You cannot pass values the second time you call storage().');
        }

        return $this->storages[$name];
    }

    public function getExtensionAlias(): string
    {
        return 'flysystem';
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('storages', $config)) {
            $this->_usedProperties['storages'] = true;
            $this->storages = array_map(fn ($v) => new \Symfony\Config\Flysystem\StorageConfig($v), $config['storages']);
            unset($config['storages']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['storages'])) {
            $output['storages'] = array_map(fn ($v) => $v->toArray(), $this->storages);
        }
        if ($this->_hasDeprecatedCalls) {
            trigger_deprecation('symfony/config', '7.4', 'Calling any fluent method on "%s" is deprecated; pass the configuration to the constructor instead.', $this::class);
        }

        return $output;
    }

}
