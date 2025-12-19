<?php

namespace Symfony\Config\SuluHttpCache;

require_once __DIR__.\DIRECTORY_SEPARATOR.'ProxyClient'.\DIRECTORY_SEPARATOR.'SymfonyConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'ProxyClient'.\DIRECTORY_SEPARATOR.'VarnishConfig.php';

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class ProxyClientConfig 
{
    private $noop;
    private $symfony;
    private $varnish;
    private $_usedProperties = [];

    /**
     * @default null
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function noop($value): static
    {
        $this->_usedProperties['noop'] = true;
        $this->noop = $value;

        return $this;
    }

    /**
     * @template TValue of array|bool
     * @param TValue $value
     * @default {"enabled":false,"servers":[],"base_url":null}
     * @return \Symfony\Config\SuluHttpCache\ProxyClient\SymfonyConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\SuluHttpCache\ProxyClient\SymfonyConfig : static)
     */
    public function symfony(array|bool $value = []): \Symfony\Config\SuluHttpCache\ProxyClient\SymfonyConfig|static
    {
        if (!\is_array($value)) {
            $this->_usedProperties['symfony'] = true;
            $this->symfony = $value;

            return $this;
        }

        if (!$this->symfony instanceof \Symfony\Config\SuluHttpCache\ProxyClient\SymfonyConfig) {
            $this->_usedProperties['symfony'] = true;
            $this->symfony = new \Symfony\Config\SuluHttpCache\ProxyClient\SymfonyConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "symfony()" has already been initialized. You cannot pass values the second time you call symfony().');
        }

        return $this->symfony;
    }

    /**
     * @template TValue of array|bool
     * @param TValue $value
     * @default {"enabled":false,"servers":[],"base_url":null,"tag_mode":"ban"}
     * @return \Symfony\Config\SuluHttpCache\ProxyClient\VarnishConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\SuluHttpCache\ProxyClient\VarnishConfig : static)
     */
    public function varnish(array|bool $value = []): \Symfony\Config\SuluHttpCache\ProxyClient\VarnishConfig|static
    {
        if (!\is_array($value)) {
            $this->_usedProperties['varnish'] = true;
            $this->varnish = $value;

            return $this;
        }

        if (!$this->varnish instanceof \Symfony\Config\SuluHttpCache\ProxyClient\VarnishConfig) {
            $this->_usedProperties['varnish'] = true;
            $this->varnish = new \Symfony\Config\SuluHttpCache\ProxyClient\VarnishConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "varnish()" has already been initialized. You cannot pass values the second time you call varnish().');
        }

        return $this->varnish;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('noop', $config)) {
            $this->_usedProperties['noop'] = true;
            $this->noop = $config['noop'];
            unset($config['noop']);
        }

        if (array_key_exists('symfony', $config)) {
            $this->_usedProperties['symfony'] = true;
            $this->symfony = \is_array($config['symfony']) ? new \Symfony\Config\SuluHttpCache\ProxyClient\SymfonyConfig($config['symfony']) : $config['symfony'];
            unset($config['symfony']);
        }

        if (array_key_exists('varnish', $config)) {
            $this->_usedProperties['varnish'] = true;
            $this->varnish = \is_array($config['varnish']) ? new \Symfony\Config\SuluHttpCache\ProxyClient\VarnishConfig($config['varnish']) : $config['varnish'];
            unset($config['varnish']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['noop'])) {
            $output['noop'] = $this->noop;
        }
        if (isset($this->_usedProperties['symfony'])) {
            $output['symfony'] = $this->symfony instanceof \Symfony\Config\SuluHttpCache\ProxyClient\SymfonyConfig ? $this->symfony->toArray() : $this->symfony;
        }
        if (isset($this->_usedProperties['varnish'])) {
            $output['varnish'] = $this->varnish instanceof \Symfony\Config\SuluHttpCache\ProxyClient\VarnishConfig ? $this->varnish->toArray() : $this->varnish;
        }

        return $output;
    }

}
