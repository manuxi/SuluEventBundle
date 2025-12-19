<?php

namespace Symfony\Config;

require_once __DIR__.\DIRECTORY_SEPARATOR.'SuluSearch'.\DIRECTORY_SEPARATOR.'AdminConfig.php';

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class SuluSearchConfig implements \Symfony\Component\Config\Builder\ConfigBuilderInterface
{
    private $admin;
    private $_usedProperties = [];
    private $_hasDeprecatedCalls = false;

    /**
     * @default {"resources":[]}
     * @deprecated since Symfony 7.4
     */
    public function admin(array $value = []): \Symfony\Config\SuluSearch\AdminConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (null === $this->admin) {
            $this->_usedProperties['admin'] = true;
            $this->admin = new \Symfony\Config\SuluSearch\AdminConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "admin()" has already been initialized. You cannot pass values the second time you call admin().');
        }

        return $this->admin;
    }

    public function getExtensionAlias(): string
    {
        return 'sulu_search';
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('admin', $config)) {
            $this->_usedProperties['admin'] = true;
            $this->admin = new \Symfony\Config\SuluSearch\AdminConfig($config['admin']);
            unset($config['admin']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['admin'])) {
            $output['admin'] = $this->admin->toArray();
        }
        if ($this->_hasDeprecatedCalls) {
            trigger_deprecation('symfony/config', '7.4', 'Calling any fluent method on "%s" is deprecated; pass the configuration to the constructor instead.', $this::class);
        }

        return $output;
    }

}
