<?php

namespace Symfony\Config;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class SuluTestConfig implements \Symfony\Component\Config\Builder\ConfigBuilderInterface
{
    private $enableTestUserProvider;
    private $_usedProperties = [];
    private $_hasDeprecatedCalls = false;

    /**
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function enableTestUserProvider($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['enableTestUserProvider'] = true;
        $this->enableTestUserProvider = $value;

        return $this;
    }

    public function getExtensionAlias(): string
    {
        return 'sulu_test';
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('enable_test_user_provider', $config)) {
            $this->_usedProperties['enableTestUserProvider'] = true;
            $this->enableTestUserProvider = $config['enable_test_user_provider'];
            unset($config['enable_test_user_provider']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['enableTestUserProvider'])) {
            $output['enable_test_user_provider'] = $this->enableTestUserProvider;
        }
        if ($this->_hasDeprecatedCalls) {
            trigger_deprecation('symfony/config', '7.4', 'Calling any fluent method on "%s" is deprecated; pass the configuration to the constructor instead.', $this::class);
        }

        return $output;
    }

}
