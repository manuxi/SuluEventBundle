<?php

namespace Symfony\Config;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class SuluPersistenceConfig implements \Symfony\Component\Config\Builder\ConfigBuilderInterface
{
    private $_hasDeprecatedCalls = false;

    public function getExtensionAlias(): string
    {
        return 'sulu_persistence';
    }

    public function __construct(array $config = [])
    {
        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if ($this->_hasDeprecatedCalls) {
            trigger_deprecation('symfony/config', '7.4', 'Calling any fluent method on "%s" is deprecated; pass the configuration to the constructor instead.', $this::class);
        }

        return $output;
    }

}
