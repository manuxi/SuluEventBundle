<?php

namespace Symfony\Config;

require_once __DIR__.\DIRECTORY_SEPARATOR.'Doctrine'.\DIRECTORY_SEPARATOR.'DbalConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Doctrine'.\DIRECTORY_SEPARATOR.'OrmConfig.php';

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class DoctrineConfig implements \Symfony\Component\Config\Builder\ConfigBuilderInterface
{
    private $dbal;
    private $orm;
    private $_usedProperties = [];
    private $_hasDeprecatedCalls = false;

    /**
     * @template TValue of mixed
     * @param TValue $value
     * @return \Symfony\Config\Doctrine\DbalConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\Doctrine\DbalConfig : static)
     * @deprecated since Symfony 7.4
     */
    public function dbal(mixed $value = []): \Symfony\Config\Doctrine\DbalConfig|static
    {
        $this->_hasDeprecatedCalls = true;
        if (!\is_array($value)) {
            $this->_usedProperties['dbal'] = true;
            $this->dbal = $value;

            return $this;
        }

        if (!$this->dbal instanceof \Symfony\Config\Doctrine\DbalConfig) {
            $this->_usedProperties['dbal'] = true;
            $this->dbal = new \Symfony\Config\Doctrine\DbalConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "dbal()" has already been initialized. You cannot pass values the second time you call dbal().');
        }

        return $this->dbal;
    }

    /**
     * @template TValue of mixed
     * @param TValue $value
     * @return \Symfony\Config\Doctrine\OrmConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\Doctrine\OrmConfig : static)
     * @deprecated since Symfony 7.4
     */
    public function orm(mixed $value = []): \Symfony\Config\Doctrine\OrmConfig|static
    {
        $this->_hasDeprecatedCalls = true;
        if (!\is_array($value)) {
            $this->_usedProperties['orm'] = true;
            $this->orm = $value;

            return $this;
        }

        if (!$this->orm instanceof \Symfony\Config\Doctrine\OrmConfig) {
            $this->_usedProperties['orm'] = true;
            $this->orm = new \Symfony\Config\Doctrine\OrmConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "orm()" has already been initialized. You cannot pass values the second time you call orm().');
        }

        return $this->orm;
    }

    public function getExtensionAlias(): string
    {
        return 'doctrine';
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('dbal', $config)) {
            $this->_usedProperties['dbal'] = true;
            $this->dbal = \is_array($config['dbal']) ? new \Symfony\Config\Doctrine\DbalConfig($config['dbal']) : $config['dbal'];
            unset($config['dbal']);
        }

        if (array_key_exists('orm', $config)) {
            $this->_usedProperties['orm'] = true;
            $this->orm = \is_array($config['orm']) ? new \Symfony\Config\Doctrine\OrmConfig($config['orm']) : $config['orm'];
            unset($config['orm']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['dbal'])) {
            $output['dbal'] = $this->dbal instanceof \Symfony\Config\Doctrine\DbalConfig ? $this->dbal->toArray() : $this->dbal;
        }
        if (isset($this->_usedProperties['orm'])) {
            $output['orm'] = $this->orm instanceof \Symfony\Config\Doctrine\OrmConfig ? $this->orm->toArray() : $this->orm;
        }
        if ($this->_hasDeprecatedCalls) {
            trigger_deprecation('symfony/config', '7.4', 'Calling any fluent method on "%s" is deprecated; pass the configuration to the constructor instead.', $this::class);
        }

        return $output;
    }

}
