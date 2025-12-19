<?php

namespace Symfony\Config\FosHttpCache;

require_once __DIR__.\DIRECTORY_SEPARATOR.'Tags'.\DIRECTORY_SEPARATOR.'RuleConfig.php';

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class TagsConfig 
{
    private $enabled;
    private $strict;
    private $expressionLanguage;
    private $responseHeader;
    private $separator;
    private $maxHeaderValueLength;
    private $rules;
    private $_usedProperties = [];

    /**
     * Allows to disable tag support. Enabled by default if you configured the cache manager and have a proxy client that supports tagging.
     * @default 'auto'
     * @param ParamConfigurator|true|false|'auto' $value
     * @return $this
     */
    public function enabled($value): static
    {
        $this->_usedProperties['enabled'] = true;
        $this->enabled = $value;

        return $this;
    }

    /**
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function strict($value): static
    {
        $this->_usedProperties['strict'] = true;
        $this->strict = $value;

        return $this;
    }

    /**
     * Service name of a custom ExpressionLanguage to use.
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function expressionLanguage($value): static
    {
        $this->_usedProperties['expressionLanguage'] = true;
        $this->expressionLanguage = $value;

        return $this;
    }

    /**
     * HTTP header that contains cache tags. Defaults to xkey-softpurge for Varnish xkey or X-Cache-Tags otherwise
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function responseHeader($value): static
    {
        $this->_usedProperties['responseHeader'] = true;
        $this->responseHeader = $value;

        return $this;
    }

    /**
     * Character(s) to use to separate multiple tags. Defaults to " " for Varnish xkey or "," otherwise
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function separator($value): static
    {
        $this->_usedProperties['separator'] = true;
        $this->separator = $value;

        return $this;
    }

    /**
     * If configured the tag header value will be split into multiple response headers of the same name (see "response_header" configuration key) that all do not exceed the configured "max_header_value_length" (recommended is 4KB = 4096) - configure in bytes.
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function maxHeaderValueLength($value): static
    {
        $this->_usedProperties['maxHeaderValueLength'] = true;
        $this->maxHeaderValueLength = $value;

        return $this;
    }

    public function rule(array $value = []): \Symfony\Config\FosHttpCache\Tags\RuleConfig
    {
        $this->_usedProperties['rules'] = true;

        return $this->rules[] = new \Symfony\Config\FosHttpCache\Tags\RuleConfig($value);
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('enabled', $config)) {
            $this->_usedProperties['enabled'] = true;
            $this->enabled = $config['enabled'];
            unset($config['enabled']);
        }

        if (array_key_exists('strict', $config)) {
            $this->_usedProperties['strict'] = true;
            $this->strict = $config['strict'];
            unset($config['strict']);
        }

        if (array_key_exists('expression_language', $config)) {
            $this->_usedProperties['expressionLanguage'] = true;
            $this->expressionLanguage = $config['expression_language'];
            unset($config['expression_language']);
        }

        if (array_key_exists('response_header', $config)) {
            $this->_usedProperties['responseHeader'] = true;
            $this->responseHeader = $config['response_header'];
            unset($config['response_header']);
        }

        if (array_key_exists('separator', $config)) {
            $this->_usedProperties['separator'] = true;
            $this->separator = $config['separator'];
            unset($config['separator']);
        }

        if (array_key_exists('max_header_value_length', $config)) {
            $this->_usedProperties['maxHeaderValueLength'] = true;
            $this->maxHeaderValueLength = $config['max_header_value_length'];
            unset($config['max_header_value_length']);
        }

        if (array_key_exists('rules', $config)) {
            $this->_usedProperties['rules'] = true;
            $this->rules = array_map(fn ($v) => new \Symfony\Config\FosHttpCache\Tags\RuleConfig($v), $config['rules']);
            unset($config['rules']);
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
        if (isset($this->_usedProperties['strict'])) {
            $output['strict'] = $this->strict;
        }
        if (isset($this->_usedProperties['expressionLanguage'])) {
            $output['expression_language'] = $this->expressionLanguage;
        }
        if (isset($this->_usedProperties['responseHeader'])) {
            $output['response_header'] = $this->responseHeader;
        }
        if (isset($this->_usedProperties['separator'])) {
            $output['separator'] = $this->separator;
        }
        if (isset($this->_usedProperties['maxHeaderValueLength'])) {
            $output['max_header_value_length'] = $this->maxHeaderValueLength;
        }
        if (isset($this->_usedProperties['rules'])) {
            $output['rules'] = array_map(fn ($v) => $v->toArray(), $this->rules);
        }

        return $output;
    }

}
