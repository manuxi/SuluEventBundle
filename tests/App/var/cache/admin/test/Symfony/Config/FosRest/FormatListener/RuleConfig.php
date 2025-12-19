<?php

namespace Symfony\Config\FosRest\FormatListener;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class RuleConfig 
{
    private $path;
    private $host;
    private $methods;
    private $attributes;
    private $stop;
    private $preferExtension;
    private $fallbackFormat;
    private $priorities;
    private $_usedProperties = [];

    /**
     * URL path info
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function path($value): static
    {
        $this->_usedProperties['path'] = true;
        $this->path = $value;

        return $this;
    }

    /**
     * URL host name
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function host($value): static
    {
        $this->_usedProperties['host'] = true;
        $this->host = $value;

        return $this;
    }

    /**
     * Method for URL
     * @default null
     * @param ParamConfigurator|mixed $value
     *
     * @return $this
     */
    public function methods(mixed $value = NULL): static
    {
        $this->_usedProperties['methods'] = true;
        $this->methods = $value;

        return $this;
    }

    /**
     * @return $this
     */
    public function attribute(string $name, mixed $value): static
    {
        $this->_usedProperties['attributes'] = true;
        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function stop($value): static
    {
        $this->_usedProperties['stop'] = true;
        $this->stop = $value;

        return $this;
    }

    /**
     * @default true
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function preferExtension($value): static
    {
        $this->_usedProperties['preferExtension'] = true;
        $this->preferExtension = $value;

        return $this;
    }

    /**
     * @default 'html'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function fallbackFormat($value): static
    {
        $this->_usedProperties['fallbackFormat'] = true;
        $this->fallbackFormat = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed>|string $value
     *
     * @return $this
     */
    public function priorities(ParamConfigurator|string|array $value): static
    {
        $this->_usedProperties['priorities'] = true;
        $this->priorities = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('path', $config)) {
            $this->_usedProperties['path'] = true;
            $this->path = $config['path'];
            unset($config['path']);
        }

        if (array_key_exists('host', $config)) {
            $this->_usedProperties['host'] = true;
            $this->host = $config['host'];
            unset($config['host']);
        }

        if (array_key_exists('methods', $config)) {
            $this->_usedProperties['methods'] = true;
            $this->methods = $config['methods'];
            unset($config['methods']);
        }

        if (array_key_exists('attributes', $config)) {
            $this->_usedProperties['attributes'] = true;
            $this->attributes = $config['attributes'];
            unset($config['attributes']);
        }

        if (array_key_exists('stop', $config)) {
            $this->_usedProperties['stop'] = true;
            $this->stop = $config['stop'];
            unset($config['stop']);
        }

        if (array_key_exists('prefer_extension', $config)) {
            $this->_usedProperties['preferExtension'] = true;
            $this->preferExtension = $config['prefer_extension'];
            unset($config['prefer_extension']);
        }

        if (array_key_exists('fallback_format', $config)) {
            $this->_usedProperties['fallbackFormat'] = true;
            $this->fallbackFormat = $config['fallback_format'];
            unset($config['fallback_format']);
        }

        if (array_key_exists('priorities', $config)) {
            $this->_usedProperties['priorities'] = true;
            $this->priorities = $config['priorities'];
            unset($config['priorities']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['path'])) {
            $output['path'] = $this->path;
        }
        if (isset($this->_usedProperties['host'])) {
            $output['host'] = $this->host;
        }
        if (isset($this->_usedProperties['methods'])) {
            $output['methods'] = $this->methods;
        }
        if (isset($this->_usedProperties['attributes'])) {
            $output['attributes'] = $this->attributes;
        }
        if (isset($this->_usedProperties['stop'])) {
            $output['stop'] = $this->stop;
        }
        if (isset($this->_usedProperties['preferExtension'])) {
            $output['prefer_extension'] = $this->preferExtension;
        }
        if (isset($this->_usedProperties['fallbackFormat'])) {
            $output['fallback_format'] = $this->fallbackFormat;
        }
        if (isset($this->_usedProperties['priorities'])) {
            $output['priorities'] = $this->priorities;
        }

        return $output;
    }

}
