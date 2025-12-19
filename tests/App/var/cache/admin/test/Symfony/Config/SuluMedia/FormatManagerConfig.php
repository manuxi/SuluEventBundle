<?php

namespace Symfony\Config\SuluMedia;

require_once __DIR__.\DIRECTORY_SEPARATOR.'FormatManager'.\DIRECTORY_SEPARATOR.'TypesConfig.php';

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class FormatManagerConfig 
{
    private $responseHeaders;
    private $defaultImagineOptions;
    private $mimeTypes;
    private $types;
    private $_usedProperties = [];

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed> $value
     *
     * @return $this
     */
    public function responseHeaders(ParamConfigurator|array $value): static
    {
        $this->_usedProperties['responseHeaders'] = true;
        $this->responseHeaders = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed> $value
     *
     * @return $this
     */
    public function defaultImagineOptions(ParamConfigurator|array $value): static
    {
        $this->_usedProperties['defaultImagineOptions'] = true;
        $this->defaultImagineOptions = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed> $value
     *
     * @return $this
     */
    public function mimeTypes(ParamConfigurator|array $value): static
    {
        $this->_usedProperties['mimeTypes'] = true;
        $this->mimeTypes = $value;

        return $this;
    }

    /**
     * @default [{"type":"document","mimeTypes":["*"]},{"type":"image","mimeTypes":["image\/*"]},{"type":"video","mimeTypes":["video\/*"]},{"type":"audio","mimeTypes":["audio\/*"]}]
     */
    public function types(array $value = []): \Symfony\Config\SuluMedia\FormatManager\TypesConfig
    {
        $this->_usedProperties['types'] = true;

        return $this->types[] = new \Symfony\Config\SuluMedia\FormatManager\TypesConfig($value);
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('response_headers', $config)) {
            $this->_usedProperties['responseHeaders'] = true;
            $this->responseHeaders = $config['response_headers'];
            unset($config['response_headers']);
        }

        if (array_key_exists('default_imagine_options', $config)) {
            $this->_usedProperties['defaultImagineOptions'] = true;
            $this->defaultImagineOptions = $config['default_imagine_options'];
            unset($config['default_imagine_options']);
        }

        if (array_key_exists('mime_types', $config)) {
            $this->_usedProperties['mimeTypes'] = true;
            $this->mimeTypes = $config['mime_types'];
            unset($config['mime_types']);
        }

        if (array_key_exists('types', $config)) {
            $this->_usedProperties['types'] = true;
            $this->types = array_map(fn ($v) => new \Symfony\Config\SuluMedia\FormatManager\TypesConfig($v), $config['types']);
            unset($config['types']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['responseHeaders'])) {
            $output['response_headers'] = $this->responseHeaders;
        }
        if (isset($this->_usedProperties['defaultImagineOptions'])) {
            $output['default_imagine_options'] = $this->defaultImagineOptions;
        }
        if (isset($this->_usedProperties['mimeTypes'])) {
            $output['mime_types'] = $this->mimeTypes;
        }
        if (isset($this->_usedProperties['types'])) {
            $output['types'] = array_map(fn ($v) => $v->toArray(), $this->types);
        }

        return $output;
    }

}
