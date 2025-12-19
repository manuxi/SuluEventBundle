<?php

namespace Symfony\Config\Framework\AssetMapper;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class PrecompressConfig 
{
    private $enabled;
    private $formats;
    private $extensions;
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
     * @param ParamConfigurator|list<ParamConfigurator|mixed> $value
     *
     * @return $this
     */
    public function formats(ParamConfigurator|array $value): static
    {
        $this->_usedProperties['formats'] = true;
        $this->formats = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed> $value
     *
     * @return $this
     */
    public function extensions(ParamConfigurator|array $value): static
    {
        $this->_usedProperties['extensions'] = true;
        $this->extensions = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('enabled', $config)) {
            $this->_usedProperties['enabled'] = true;
            $this->enabled = $config['enabled'];
            unset($config['enabled']);
        }

        if (array_key_exists('formats', $config)) {
            $this->_usedProperties['formats'] = true;
            $this->formats = $config['formats'];
            unset($config['formats']);
        }

        if (array_key_exists('extensions', $config)) {
            $this->_usedProperties['extensions'] = true;
            $this->extensions = $config['extensions'];
            unset($config['extensions']);
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
        if (isset($this->_usedProperties['formats'])) {
            $output['formats'] = $this->formats;
        }
        if (isset($this->_usedProperties['extensions'])) {
            $output['extensions'] = $this->extensions;
        }

        return $output;
    }

}
