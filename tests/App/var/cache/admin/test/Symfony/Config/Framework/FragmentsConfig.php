<?php

namespace Symfony\Config\Framework;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class FragmentsConfig 
{
    private $enabled;
    private $hincludeDefaultTemplate;
    private $path;
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
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function hincludeDefaultTemplate($value): static
    {
        $this->_usedProperties['hincludeDefaultTemplate'] = true;
        $this->hincludeDefaultTemplate = $value;

        return $this;
    }

    /**
     * @default '/_fragment'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function path($value): static
    {
        $this->_usedProperties['path'] = true;
        $this->path = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('enabled', $config)) {
            $this->_usedProperties['enabled'] = true;
            $this->enabled = $config['enabled'];
            unset($config['enabled']);
        }

        if (array_key_exists('hinclude_default_template', $config)) {
            $this->_usedProperties['hincludeDefaultTemplate'] = true;
            $this->hincludeDefaultTemplate = $config['hinclude_default_template'];
            unset($config['hinclude_default_template']);
        }

        if (array_key_exists('path', $config)) {
            $this->_usedProperties['path'] = true;
            $this->path = $config['path'];
            unset($config['path']);
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
        if (isset($this->_usedProperties['hincludeDefaultTemplate'])) {
            $output['hinclude_default_template'] = $this->hincludeDefaultTemplate;
        }
        if (isset($this->_usedProperties['path'])) {
            $output['path'] = $this->path;
        }

        return $output;
    }

}
