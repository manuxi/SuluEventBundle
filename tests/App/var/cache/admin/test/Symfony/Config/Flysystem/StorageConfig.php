<?php

namespace Symfony\Config\Flysystem;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class StorageConfig 
{
    private $adapter;
    private $options;
    private $visibility;
    private $directoryVisibility;
    private $retainVisibility;
    private $caseSensitive;
    private $disableAsserts;
    private $publicUrl;
    private $pathNormalizer;
    private $publicUrlGenerator;
    private $temporaryUrlGenerator;
    private $readOnly;
    private $_usedProperties = [];

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function adapter($value): static
    {
        $this->_usedProperties['adapter'] = true;
        $this->adapter = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed> $value
     *
     * @return $this
     */
    public function options(ParamConfigurator|array $value): static
    {
        $this->_usedProperties['options'] = true;
        $this->options = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function visibility($value): static
    {
        $this->_usedProperties['visibility'] = true;
        $this->visibility = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function directoryVisibility($value): static
    {
        $this->_usedProperties['directoryVisibility'] = true;
        $this->directoryVisibility = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function retainVisibility($value): static
    {
        $this->_usedProperties['retainVisibility'] = true;
        $this->retainVisibility = $value;

        return $this;
    }

    /**
     * @default true
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function caseSensitive($value): static
    {
        $this->_usedProperties['caseSensitive'] = true;
        $this->caseSensitive = $value;

        return $this;
    }

    /**
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function disableAsserts($value): static
    {
        $this->_usedProperties['disableAsserts'] = true;
        $this->disableAsserts = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed>|mixed $value
     *
     * @return $this
     */
    public function publicUrl(mixed $value): static
    {
        $this->_usedProperties['publicUrl'] = true;
        $this->publicUrl = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function pathNormalizer($value): static
    {
        $this->_usedProperties['pathNormalizer'] = true;
        $this->pathNormalizer = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function publicUrlGenerator($value): static
    {
        $this->_usedProperties['publicUrlGenerator'] = true;
        $this->publicUrlGenerator = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function temporaryUrlGenerator($value): static
    {
        $this->_usedProperties['temporaryUrlGenerator'] = true;
        $this->temporaryUrlGenerator = $value;

        return $this;
    }

    /**
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function readOnly($value): static
    {
        $this->_usedProperties['readOnly'] = true;
        $this->readOnly = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('adapter', $config)) {
            $this->_usedProperties['adapter'] = true;
            $this->adapter = $config['adapter'];
            unset($config['adapter']);
        }

        if (array_key_exists('options', $config)) {
            $this->_usedProperties['options'] = true;
            $this->options = $config['options'];
            unset($config['options']);
        }

        if (array_key_exists('visibility', $config)) {
            $this->_usedProperties['visibility'] = true;
            $this->visibility = $config['visibility'];
            unset($config['visibility']);
        }

        if (array_key_exists('directory_visibility', $config)) {
            $this->_usedProperties['directoryVisibility'] = true;
            $this->directoryVisibility = $config['directory_visibility'];
            unset($config['directory_visibility']);
        }

        if (array_key_exists('retain_visibility', $config)) {
            $this->_usedProperties['retainVisibility'] = true;
            $this->retainVisibility = $config['retain_visibility'];
            unset($config['retain_visibility']);
        }

        if (array_key_exists('case_sensitive', $config)) {
            $this->_usedProperties['caseSensitive'] = true;
            $this->caseSensitive = $config['case_sensitive'];
            unset($config['case_sensitive']);
        }

        if (array_key_exists('disable_asserts', $config)) {
            $this->_usedProperties['disableAsserts'] = true;
            $this->disableAsserts = $config['disable_asserts'];
            unset($config['disable_asserts']);
        }

        if (array_key_exists('public_url', $config)) {
            $this->_usedProperties['publicUrl'] = true;
            $this->publicUrl = $config['public_url'];
            unset($config['public_url']);
        }

        if (array_key_exists('path_normalizer', $config)) {
            $this->_usedProperties['pathNormalizer'] = true;
            $this->pathNormalizer = $config['path_normalizer'];
            unset($config['path_normalizer']);
        }

        if (array_key_exists('public_url_generator', $config)) {
            $this->_usedProperties['publicUrlGenerator'] = true;
            $this->publicUrlGenerator = $config['public_url_generator'];
            unset($config['public_url_generator']);
        }

        if (array_key_exists('temporary_url_generator', $config)) {
            $this->_usedProperties['temporaryUrlGenerator'] = true;
            $this->temporaryUrlGenerator = $config['temporary_url_generator'];
            unset($config['temporary_url_generator']);
        }

        if (array_key_exists('read_only', $config)) {
            $this->_usedProperties['readOnly'] = true;
            $this->readOnly = $config['read_only'];
            unset($config['read_only']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['adapter'])) {
            $output['adapter'] = $this->adapter;
        }
        if (isset($this->_usedProperties['options'])) {
            $output['options'] = $this->options;
        }
        if (isset($this->_usedProperties['visibility'])) {
            $output['visibility'] = $this->visibility;
        }
        if (isset($this->_usedProperties['directoryVisibility'])) {
            $output['directory_visibility'] = $this->directoryVisibility;
        }
        if (isset($this->_usedProperties['retainVisibility'])) {
            $output['retain_visibility'] = $this->retainVisibility;
        }
        if (isset($this->_usedProperties['caseSensitive'])) {
            $output['case_sensitive'] = $this->caseSensitive;
        }
        if (isset($this->_usedProperties['disableAsserts'])) {
            $output['disable_asserts'] = $this->disableAsserts;
        }
        if (isset($this->_usedProperties['publicUrl'])) {
            $output['public_url'] = $this->publicUrl;
        }
        if (isset($this->_usedProperties['pathNormalizer'])) {
            $output['path_normalizer'] = $this->pathNormalizer;
        }
        if (isset($this->_usedProperties['publicUrlGenerator'])) {
            $output['public_url_generator'] = $this->publicUrlGenerator;
        }
        if (isset($this->_usedProperties['temporaryUrlGenerator'])) {
            $output['temporary_url_generator'] = $this->temporaryUrlGenerator;
        }
        if (isset($this->_usedProperties['readOnly'])) {
            $output['read_only'] = $this->readOnly;
        }

        return $output;
    }

}
