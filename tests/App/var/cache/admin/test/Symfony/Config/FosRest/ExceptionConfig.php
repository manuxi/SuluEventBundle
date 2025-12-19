<?php

namespace Symfony\Config\FosRest;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class ExceptionConfig 
{
    private $enabled;
    private $mapExceptionCodes;
    private $exceptionListener;
    private $serializeExceptions;
    private $flattenExceptionFormat;
    private $serializerErrorRenderer;
    private $codes;
    private $messages;
    private $debug;
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
     * Enables an event listener that maps exception codes to response status codes based on the map configured with the "fos_rest.exception.codes" option.
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function mapExceptionCodes($value): static
    {
        $this->_usedProperties['mapExceptionCodes'] = true;
        $this->mapExceptionCodes = $value;

        return $this;
    }

    /**
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function exceptionListener($value): static
    {
        $this->_usedProperties['exceptionListener'] = true;
        $this->exceptionListener = $value;

        return $this;
    }

    /**
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function serializeExceptions($value): static
    {
        $this->_usedProperties['serializeExceptions'] = true;
        $this->serializeExceptions = $value;

        return $this;
    }

    /**
     * @default 'legacy'
     * @param ParamConfigurator|'legacy'|'rfc7807' $value
     * @return $this
     */
    public function flattenExceptionFormat($value): static
    {
        $this->_usedProperties['flattenExceptionFormat'] = true;
        $this->flattenExceptionFormat = $value;

        return $this;
    }

    /**
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function serializerErrorRenderer($value): static
    {
        $this->_usedProperties['serializerErrorRenderer'] = true;
        $this->serializerErrorRenderer = $value;

        return $this;
    }

    /**
     * @return $this
     */
    public function code(string $name, ParamConfigurator|int $value): static
    {
        $this->_usedProperties['codes'] = true;
        $this->codes[$name] = $value;

        return $this;
    }

    /**
     * @return $this
     */
    public function message(string $name, ParamConfigurator|bool $value): static
    {
        $this->_usedProperties['messages'] = true;
        $this->messages[$name] = $value;

        return $this;
    }

    /**
     * @default true
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function debug($value): static
    {
        $this->_usedProperties['debug'] = true;
        $this->debug = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('enabled', $config)) {
            $this->_usedProperties['enabled'] = true;
            $this->enabled = $config['enabled'];
            unset($config['enabled']);
        }

        if (array_key_exists('map_exception_codes', $config)) {
            $this->_usedProperties['mapExceptionCodes'] = true;
            $this->mapExceptionCodes = $config['map_exception_codes'];
            unset($config['map_exception_codes']);
        }

        if (array_key_exists('exception_listener', $config)) {
            $this->_usedProperties['exceptionListener'] = true;
            $this->exceptionListener = $config['exception_listener'];
            unset($config['exception_listener']);
        }

        if (array_key_exists('serialize_exceptions', $config)) {
            $this->_usedProperties['serializeExceptions'] = true;
            $this->serializeExceptions = $config['serialize_exceptions'];
            unset($config['serialize_exceptions']);
        }

        if (array_key_exists('flatten_exception_format', $config)) {
            $this->_usedProperties['flattenExceptionFormat'] = true;
            $this->flattenExceptionFormat = $config['flatten_exception_format'];
            unset($config['flatten_exception_format']);
        }

        if (array_key_exists('serializer_error_renderer', $config)) {
            $this->_usedProperties['serializerErrorRenderer'] = true;
            $this->serializerErrorRenderer = $config['serializer_error_renderer'];
            unset($config['serializer_error_renderer']);
        }

        if (array_key_exists('codes', $config)) {
            $this->_usedProperties['codes'] = true;
            $this->codes = $config['codes'];
            unset($config['codes']);
        }

        if (array_key_exists('messages', $config)) {
            $this->_usedProperties['messages'] = true;
            $this->messages = $config['messages'];
            unset($config['messages']);
        }

        if (array_key_exists('debug', $config)) {
            $this->_usedProperties['debug'] = true;
            $this->debug = $config['debug'];
            unset($config['debug']);
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
        if (isset($this->_usedProperties['mapExceptionCodes'])) {
            $output['map_exception_codes'] = $this->mapExceptionCodes;
        }
        if (isset($this->_usedProperties['exceptionListener'])) {
            $output['exception_listener'] = $this->exceptionListener;
        }
        if (isset($this->_usedProperties['serializeExceptions'])) {
            $output['serialize_exceptions'] = $this->serializeExceptions;
        }
        if (isset($this->_usedProperties['flattenExceptionFormat'])) {
            $output['flatten_exception_format'] = $this->flattenExceptionFormat;
        }
        if (isset($this->_usedProperties['serializerErrorRenderer'])) {
            $output['serializer_error_renderer'] = $this->serializerErrorRenderer;
        }
        if (isset($this->_usedProperties['codes'])) {
            $output['codes'] = $this->codes;
        }
        if (isset($this->_usedProperties['messages'])) {
            $output['messages'] = $this->messages;
        }
        if (isset($this->_usedProperties['debug'])) {
            $output['debug'] = $this->debug;
        }

        return $output;
    }

}
