<?php

namespace Symfony\Config;

require_once __DIR__.\DIRECTORY_SEPARATOR.'SuluTag'.\DIRECTORY_SEPARATOR.'ObjectsConfig.php';

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class SuluTagConfig implements \Symfony\Component\Config\Builder\ConfigBuilderInterface
{
    private $objects;
    private $_usedProperties = [];
    private $_hasDeprecatedCalls = false;

    /**
     * @default {"tag":{"model":"Sulu\\Bundle\\TagBundle\\Entity\\Tag","repository":"Sulu\\Bundle\\TagBundle\\Entity\\TagRepository"}}
     * @deprecated since Symfony 7.4
     */
    public function objects(array $value = []): \Symfony\Config\SuluTag\ObjectsConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (null === $this->objects) {
            $this->_usedProperties['objects'] = true;
            $this->objects = new \Symfony\Config\SuluTag\ObjectsConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "objects()" has already been initialized. You cannot pass values the second time you call objects().');
        }

        return $this->objects;
    }

    public function getExtensionAlias(): string
    {
        return 'sulu_tag';
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('objects', $config)) {
            $this->_usedProperties['objects'] = true;
            $this->objects = new \Symfony\Config\SuluTag\ObjectsConfig($config['objects']);
            unset($config['objects']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['objects'])) {
            $output['objects'] = $this->objects->toArray();
        }
        if ($this->_hasDeprecatedCalls) {
            trigger_deprecation('symfony/config', '7.4', 'Calling any fluent method on "%s" is deprecated; pass the configuration to the constructor instead.', $this::class);
        }

        return $output;
    }

}
