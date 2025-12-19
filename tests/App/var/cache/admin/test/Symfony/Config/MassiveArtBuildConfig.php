<?php

namespace Symfony\Config;

require_once __DIR__.\DIRECTORY_SEPARATOR.'MassiveArtBuild'.\DIRECTORY_SEPARATOR.'TargetsConfig.php';

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class MassiveArtBuildConfig implements \Symfony\Component\Config\Builder\ConfigBuilderInterface
{
    private $commandClass;
    private $targets;
    private $_usedProperties = [];
    private $_hasDeprecatedCalls = false;

    /**
     * @default 'Massive\\Bundle\\BuildBundle\\Command\\BuildCommand'
     * @param ParamConfigurator|mixed $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function commandClass($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['commandClass'] = true;
        $this->commandClass = $value;

        return $this;
    }

    /**
     * @deprecated since Symfony 7.4
     */
    public function targets(string $name, array $value = []): \Symfony\Config\MassiveArtBuild\TargetsConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (!isset($this->targets[$name])) {
            $this->_usedProperties['targets'] = true;
            $this->targets[$name] = new \Symfony\Config\MassiveArtBuild\TargetsConfig($value);
        } elseif (1 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "targets()" has already been initialized. You cannot pass values the second time you call targets().');
        }

        return $this->targets[$name];
    }

    public function getExtensionAlias(): string
    {
        return 'massive_art_build';
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('command_class', $config)) {
            $this->_usedProperties['commandClass'] = true;
            $this->commandClass = $config['command_class'];
            unset($config['command_class']);
        }

        if (array_key_exists('targets', $config)) {
            $this->_usedProperties['targets'] = true;
            $this->targets = array_map(fn ($v) => new \Symfony\Config\MassiveArtBuild\TargetsConfig($v), $config['targets']);
            unset($config['targets']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['commandClass'])) {
            $output['command_class'] = $this->commandClass;
        }
        if (isset($this->_usedProperties['targets'])) {
            $output['targets'] = array_map(fn ($v) => $v->toArray(), $this->targets);
        }
        if ($this->_hasDeprecatedCalls) {
            trigger_deprecation('symfony/config', '7.4', 'Calling any fluent method on "%s" is deprecated; pass the configuration to the constructor instead.', $this::class);
        }

        return $output;
    }

}
