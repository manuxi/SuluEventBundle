<?php

namespace Symfony\Config\Framework;

require_once __DIR__.\DIRECTORY_SEPARATOR.'Workflows'.\DIRECTORY_SEPARATOR.'WorkflowsConfig.php';

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class WorkflowsConfig 
{
    private $enabled;
    private $workflows;
    private $_usedProperties = [];

    /**
     * @default false
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
     * @template TValue of mixed
     * @param TValue $value
     * @return \Symfony\Config\Framework\Workflows\WorkflowsConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\Framework\Workflows\WorkflowsConfig : static)
     */
    public function workflows(string $name, mixed $value = []): \Symfony\Config\Framework\Workflows\WorkflowsConfig|static
    {
        if (!\is_array($value)) {
            $this->_usedProperties['workflows'] = true;
            $this->workflows[$name] = $value;

            return $this;
        }

        if (!isset($this->workflows[$name]) || !$this->workflows[$name] instanceof \Symfony\Config\Framework\Workflows\WorkflowsConfig) {
            $this->_usedProperties['workflows'] = true;
            $this->workflows[$name] = new \Symfony\Config\Framework\Workflows\WorkflowsConfig($value);
        } elseif (1 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "workflows()" has already been initialized. You cannot pass values the second time you call workflows().');
        }

        return $this->workflows[$name];
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('enabled', $config)) {
            $this->_usedProperties['enabled'] = true;
            $this->enabled = $config['enabled'];
            unset($config['enabled']);
        }

        if (array_key_exists('workflows', $config)) {
            $this->_usedProperties['workflows'] = true;
            $this->workflows = array_map(fn ($v) => \is_array($v) ? new \Symfony\Config\Framework\Workflows\WorkflowsConfig($v) : $v, $config['workflows']);
            unset($config['workflows']);
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
        if (isset($this->_usedProperties['workflows'])) {
            $output['workflows'] = array_map(fn ($v) => $v instanceof \Symfony\Config\Framework\Workflows\WorkflowsConfig ? $v->toArray() : $v, $this->workflows);
        }

        return $output;
    }

}
