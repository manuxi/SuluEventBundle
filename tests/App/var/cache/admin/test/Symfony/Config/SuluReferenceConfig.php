<?php

namespace Symfony\Config;

require_once __DIR__.\DIRECTORY_SEPARATOR.'SuluReference'.\DIRECTORY_SEPARATOR.'ObjectsConfig.php';

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class SuluReferenceConfig implements \Symfony\Component\Config\Builder\ConfigBuilderInterface
{
    private $objects;
    private $_usedProperties = [];
    private $_hasDeprecatedCalls = false;

    /**
     * @default {"reference":{"model":"Sulu\\Bundle\\ReferenceBundle\\Domain\\Model\\Reference"}}
     * @deprecated since Symfony 7.4
     */
    public function objects(array $value = []): \Symfony\Config\SuluReference\ObjectsConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (null === $this->objects) {
            $this->_usedProperties['objects'] = true;
            $this->objects = new \Symfony\Config\SuluReference\ObjectsConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "objects()" has already been initialized. You cannot pass values the second time you call objects().');
        }

        return $this->objects;
    }

    public function getExtensionAlias(): string
    {
        return 'sulu_reference';
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('objects', $config)) {
            $this->_usedProperties['objects'] = true;
            $this->objects = new \Symfony\Config\SuluReference\ObjectsConfig($config['objects']);
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
