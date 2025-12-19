<?php

namespace Symfony\Config\SuluMedia;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class FormatCacheConfig 
{
    private $path;
    private $saveImage;
    private $segments;
    private $_usedProperties = [];

    /**
     * @default '%kernel.project_dir%/public/uploads/media'
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
     * @default true
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function saveImage($value): static
    {
        $this->_usedProperties['saveImage'] = true;
        $this->saveImage = $value;

        return $this;
    }

    /**
     * @default 10
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function segments($value): static
    {
        $this->_usedProperties['segments'] = true;
        $this->segments = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('path', $config)) {
            $this->_usedProperties['path'] = true;
            $this->path = $config['path'];
            unset($config['path']);
        }

        if (array_key_exists('save_image', $config)) {
            $this->_usedProperties['saveImage'] = true;
            $this->saveImage = $config['save_image'];
            unset($config['save_image']);
        }

        if (array_key_exists('segments', $config)) {
            $this->_usedProperties['segments'] = true;
            $this->segments = $config['segments'];
            unset($config['segments']);
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
        if (isset($this->_usedProperties['saveImage'])) {
            $output['save_image'] = $this->saveImage;
        }
        if (isset($this->_usedProperties['segments'])) {
            $output['segments'] = $this->segments;
        }

        return $output;
    }

}
