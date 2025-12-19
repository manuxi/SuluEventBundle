<?php

namespace Symfony\Config\SuluPreview\Cache;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class FileSystemConfig 
{
    private $directory;
    private $extension;
    private $umask;
    private $_usedProperties = [];

    /**
     * @default '%sulu.cache_dir%/preview'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function directory($value): static
    {
        $this->_usedProperties['directory'] = true;
        $this->directory = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function extension($value): static
    {
        $this->_usedProperties['extension'] = true;
        $this->extension = $value;

        return $this;
    }

    /**
     * @default 2
     * @param ParamConfigurator|int $value
     * @return $this
     */
    public function umask($value): static
    {
        $this->_usedProperties['umask'] = true;
        $this->umask = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('directory', $config)) {
            $this->_usedProperties['directory'] = true;
            $this->directory = $config['directory'];
            unset($config['directory']);
        }

        if (array_key_exists('extension', $config)) {
            $this->_usedProperties['extension'] = true;
            $this->extension = $config['extension'];
            unset($config['extension']);
        }

        if (array_key_exists('umask', $config)) {
            $this->_usedProperties['umask'] = true;
            $this->umask = $config['umask'];
            unset($config['umask']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['directory'])) {
            $output['directory'] = $this->directory;
        }
        if (isset($this->_usedProperties['extension'])) {
            $output['extension'] = $this->extension;
        }
        if (isset($this->_usedProperties['umask'])) {
            $output['umask'] = $this->umask;
        }

        return $output;
    }

}
