<?php

namespace Symfony\Config\FosRest;

require_once __DIR__.\DIRECTORY_SEPARATOR.'BodyListener'.\DIRECTORY_SEPARATOR.'ArrayNormalizerConfig.php';

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class BodyListenerConfig 
{
    private $enabled;
    private $service;
    private $defaultFormat;
    private $throwExceptionOnUnsupportedContentType;
    private $decoders;
    private $arrayNormalizer;
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
    public function service($value): static
    {
        $this->_usedProperties['service'] = true;
        $this->service = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function defaultFormat($value): static
    {
        $this->_usedProperties['defaultFormat'] = true;
        $this->defaultFormat = $value;

        return $this;
    }

    /**
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function throwExceptionOnUnsupportedContentType($value): static
    {
        $this->_usedProperties['throwExceptionOnUnsupportedContentType'] = true;
        $this->throwExceptionOnUnsupportedContentType = $value;

        return $this;
    }

    /**
     * @return $this
     */
    public function decoder(string $name, mixed $value): static
    {
        $this->_usedProperties['decoders'] = true;
        $this->decoders[$name] = $value;

        return $this;
    }

    /**
     * @template TValue of string|array
     * @param TValue $value
     * @default {"service":null,"forms":false}
     * @return \Symfony\Config\FosRest\BodyListener\ArrayNormalizerConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\FosRest\BodyListener\ArrayNormalizerConfig : static)
     */
    public function arrayNormalizer(string|array $value = []): \Symfony\Config\FosRest\BodyListener\ArrayNormalizerConfig|static
    {
        if (!\is_array($value)) {
            $this->_usedProperties['arrayNormalizer'] = true;
            $this->arrayNormalizer = $value;

            return $this;
        }

        if (!$this->arrayNormalizer instanceof \Symfony\Config\FosRest\BodyListener\ArrayNormalizerConfig) {
            $this->_usedProperties['arrayNormalizer'] = true;
            $this->arrayNormalizer = new \Symfony\Config\FosRest\BodyListener\ArrayNormalizerConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "arrayNormalizer()" has already been initialized. You cannot pass values the second time you call arrayNormalizer().');
        }

        return $this->arrayNormalizer;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('enabled', $config)) {
            $this->_usedProperties['enabled'] = true;
            $this->enabled = $config['enabled'];
            unset($config['enabled']);
        }

        if (array_key_exists('service', $config)) {
            $this->_usedProperties['service'] = true;
            $this->service = $config['service'];
            unset($config['service']);
        }

        if (array_key_exists('default_format', $config)) {
            $this->_usedProperties['defaultFormat'] = true;
            $this->defaultFormat = $config['default_format'];
            unset($config['default_format']);
        }

        if (array_key_exists('throw_exception_on_unsupported_content_type', $config)) {
            $this->_usedProperties['throwExceptionOnUnsupportedContentType'] = true;
            $this->throwExceptionOnUnsupportedContentType = $config['throw_exception_on_unsupported_content_type'];
            unset($config['throw_exception_on_unsupported_content_type']);
        }

        if (array_key_exists('decoders', $config)) {
            $this->_usedProperties['decoders'] = true;
            $this->decoders = $config['decoders'];
            unset($config['decoders']);
        }

        if (array_key_exists('array_normalizer', $config)) {
            $this->_usedProperties['arrayNormalizer'] = true;
            $this->arrayNormalizer = \is_array($config['array_normalizer']) ? new \Symfony\Config\FosRest\BodyListener\ArrayNormalizerConfig($config['array_normalizer']) : $config['array_normalizer'];
            unset($config['array_normalizer']);
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
        if (isset($this->_usedProperties['service'])) {
            $output['service'] = $this->service;
        }
        if (isset($this->_usedProperties['defaultFormat'])) {
            $output['default_format'] = $this->defaultFormat;
        }
        if (isset($this->_usedProperties['throwExceptionOnUnsupportedContentType'])) {
            $output['throw_exception_on_unsupported_content_type'] = $this->throwExceptionOnUnsupportedContentType;
        }
        if (isset($this->_usedProperties['decoders'])) {
            $output['decoders'] = $this->decoders;
        }
        if (isset($this->_usedProperties['arrayNormalizer'])) {
            $output['array_normalizer'] = $this->arrayNormalizer instanceof \Symfony\Config\FosRest\BodyListener\ArrayNormalizerConfig ? $this->arrayNormalizer->toArray() : $this->arrayNormalizer;
        }

        return $output;
    }

}
