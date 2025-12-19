<?php

namespace Symfony\Config\StofDoctrineExtensions;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class UploadableConfig 
{
    private $defaultFilePath;
    private $mimeTypeGuesserClass;
    private $defaultFileInfoClass;
    private $validateWritableDirectory;
    private $_usedProperties = [];

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function defaultFilePath($value): static
    {
        $this->_usedProperties['defaultFilePath'] = true;
        $this->defaultFilePath = $value;

        return $this;
    }

    /**
     * @default 'Stof\\DoctrineExtensionsBundle\\Uploadable\\MimeTypeGuesserAdapter'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function mimeTypeGuesserClass($value): static
    {
        $this->_usedProperties['mimeTypeGuesserClass'] = true;
        $this->mimeTypeGuesserClass = $value;

        return $this;
    }

    /**
     * @default 'Stof\\DoctrineExtensionsBundle\\Uploadable\\UploadedFileInfo'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function defaultFileInfoClass($value): static
    {
        $this->_usedProperties['defaultFileInfoClass'] = true;
        $this->defaultFileInfoClass = $value;

        return $this;
    }

    /**
     * @default true
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function validateWritableDirectory($value): static
    {
        $this->_usedProperties['validateWritableDirectory'] = true;
        $this->validateWritableDirectory = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('default_file_path', $config)) {
            $this->_usedProperties['defaultFilePath'] = true;
            $this->defaultFilePath = $config['default_file_path'];
            unset($config['default_file_path']);
        }

        if (array_key_exists('mime_type_guesser_class', $config)) {
            $this->_usedProperties['mimeTypeGuesserClass'] = true;
            $this->mimeTypeGuesserClass = $config['mime_type_guesser_class'];
            unset($config['mime_type_guesser_class']);
        }

        if (array_key_exists('default_file_info_class', $config)) {
            $this->_usedProperties['defaultFileInfoClass'] = true;
            $this->defaultFileInfoClass = $config['default_file_info_class'];
            unset($config['default_file_info_class']);
        }

        if (array_key_exists('validate_writable_directory', $config)) {
            $this->_usedProperties['validateWritableDirectory'] = true;
            $this->validateWritableDirectory = $config['validate_writable_directory'];
            unset($config['validate_writable_directory']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['defaultFilePath'])) {
            $output['default_file_path'] = $this->defaultFilePath;
        }
        if (isset($this->_usedProperties['mimeTypeGuesserClass'])) {
            $output['mime_type_guesser_class'] = $this->mimeTypeGuesserClass;
        }
        if (isset($this->_usedProperties['defaultFileInfoClass'])) {
            $output['default_file_info_class'] = $this->defaultFileInfoClass;
        }
        if (isset($this->_usedProperties['validateWritableDirectory'])) {
            $output['validate_writable_directory'] = $this->validateWritableDirectory;
        }

        return $output;
    }

}
