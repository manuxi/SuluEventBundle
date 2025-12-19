<?php

namespace Symfony\Config\FosRest;

require_once __DIR__.\DIRECTORY_SEPARATOR.'View'.\DIRECTORY_SEPARATOR.'MimeTypesConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'View'.\DIRECTORY_SEPARATOR.'ViewResponseListenerConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'View'.\DIRECTORY_SEPARATOR.'JsonpHandlerConfig.php';

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Loader\ParamConfigurator;

/**
 * This class is automatically generated to help in creating a config.
 */
class ViewConfig 
{
    private $mimeTypes;
    private $formats;
    private $viewResponseListener;
    private $failedValidation;
    private $emptyContent;
    private $serializeNull;
    private $jsonpHandler;
    private $_usedProperties = [];

    /**
     * @template TValue of array|bool
     * @param TValue $value
     * @default {"enabled":false,"service":null,"formats":[]}
     * @return \Symfony\Config\FosRest\View\MimeTypesConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\FosRest\View\MimeTypesConfig : static)
     */
    public function mimeTypes(array|bool $value = []): \Symfony\Config\FosRest\View\MimeTypesConfig|static
    {
        if (!\is_array($value)) {
            $this->_usedProperties['mimeTypes'] = true;
            $this->mimeTypes = $value;

            return $this;
        }

        if (!$this->mimeTypes instanceof \Symfony\Config\FosRest\View\MimeTypesConfig) {
            $this->_usedProperties['mimeTypes'] = true;
            $this->mimeTypes = new \Symfony\Config\FosRest\View\MimeTypesConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "mimeTypes()" has already been initialized. You cannot pass values the second time you call mimeTypes().');
        }

        return $this->mimeTypes;
    }

    /**
     * @return $this
     */
    public function format(string $name, ParamConfigurator|bool $value): static
    {
        $this->_usedProperties['formats'] = true;
        $this->formats[$name] = $value;

        return $this;
    }

    /**
     * @template TValue of string|array|bool
     * @param TValue $value
     * @default {"enabled":false,"force":false,"service":null}
     * @return \Symfony\Config\FosRest\View\ViewResponseListenerConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\FosRest\View\ViewResponseListenerConfig : static)
     */
    public function viewResponseListener(string|array|bool $value = []): \Symfony\Config\FosRest\View\ViewResponseListenerConfig|static
    {
        if (!\is_array($value)) {
            $this->_usedProperties['viewResponseListener'] = true;
            $this->viewResponseListener = $value;

            return $this;
        }

        if (!$this->viewResponseListener instanceof \Symfony\Config\FosRest\View\ViewResponseListenerConfig) {
            $this->_usedProperties['viewResponseListener'] = true;
            $this->viewResponseListener = new \Symfony\Config\FosRest\View\ViewResponseListenerConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "viewResponseListener()" has already been initialized. You cannot pass values the second time you call viewResponseListener().');
        }

        return $this->viewResponseListener;
    }

    /**
     * @default 400
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function failedValidation($value): static
    {
        $this->_usedProperties['failedValidation'] = true;
        $this->failedValidation = $value;

        return $this;
    }

    /**
     * @default 204
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function emptyContent($value): static
    {
        $this->_usedProperties['emptyContent'] = true;
        $this->emptyContent = $value;

        return $this;
    }

    /**
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function serializeNull($value): static
    {
        $this->_usedProperties['serializeNull'] = true;
        $this->serializeNull = $value;

        return $this;
    }

    public function jsonpHandler(array $value = []): \Symfony\Config\FosRest\View\JsonpHandlerConfig
    {
        if (null === $this->jsonpHandler) {
            $this->_usedProperties['jsonpHandler'] = true;
            $this->jsonpHandler = new \Symfony\Config\FosRest\View\JsonpHandlerConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "jsonpHandler()" has already been initialized. You cannot pass values the second time you call jsonpHandler().');
        }

        return $this->jsonpHandler;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('mime_types', $config)) {
            $this->_usedProperties['mimeTypes'] = true;
            $this->mimeTypes = \is_array($config['mime_types']) ? new \Symfony\Config\FosRest\View\MimeTypesConfig($config['mime_types']) : $config['mime_types'];
            unset($config['mime_types']);
        }

        if (array_key_exists('formats', $config)) {
            $this->_usedProperties['formats'] = true;
            $this->formats = $config['formats'];
            unset($config['formats']);
        }

        if (array_key_exists('view_response_listener', $config)) {
            $this->_usedProperties['viewResponseListener'] = true;
            $this->viewResponseListener = \is_array($config['view_response_listener']) ? new \Symfony\Config\FosRest\View\ViewResponseListenerConfig($config['view_response_listener']) : $config['view_response_listener'];
            unset($config['view_response_listener']);
        }

        if (array_key_exists('failed_validation', $config)) {
            $this->_usedProperties['failedValidation'] = true;
            $this->failedValidation = $config['failed_validation'];
            unset($config['failed_validation']);
        }

        if (array_key_exists('empty_content', $config)) {
            $this->_usedProperties['emptyContent'] = true;
            $this->emptyContent = $config['empty_content'];
            unset($config['empty_content']);
        }

        if (array_key_exists('serialize_null', $config)) {
            $this->_usedProperties['serializeNull'] = true;
            $this->serializeNull = $config['serialize_null'];
            unset($config['serialize_null']);
        }

        if (array_key_exists('jsonp_handler', $config)) {
            $this->_usedProperties['jsonpHandler'] = true;
            $this->jsonpHandler = new \Symfony\Config\FosRest\View\JsonpHandlerConfig($config['jsonp_handler']);
            unset($config['jsonp_handler']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['mimeTypes'])) {
            $output['mime_types'] = $this->mimeTypes instanceof \Symfony\Config\FosRest\View\MimeTypesConfig ? $this->mimeTypes->toArray() : $this->mimeTypes;
        }
        if (isset($this->_usedProperties['formats'])) {
            $output['formats'] = $this->formats;
        }
        if (isset($this->_usedProperties['viewResponseListener'])) {
            $output['view_response_listener'] = $this->viewResponseListener instanceof \Symfony\Config\FosRest\View\ViewResponseListenerConfig ? $this->viewResponseListener->toArray() : $this->viewResponseListener;
        }
        if (isset($this->_usedProperties['failedValidation'])) {
            $output['failed_validation'] = $this->failedValidation;
        }
        if (isset($this->_usedProperties['emptyContent'])) {
            $output['empty_content'] = $this->emptyContent;
        }
        if (isset($this->_usedProperties['serializeNull'])) {
            $output['serialize_null'] = $this->serializeNull;
        }
        if (isset($this->_usedProperties['jsonpHandler'])) {
            $output['jsonp_handler'] = $this->jsonpHandler->toArray();
        }

        return $output;
    }

}
