<?php

namespace Symfony\Config\SuluMedia;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class UploadConfig 
{
    private $maxFilesize;
    private $blockedFileTypes;
    private $_usedProperties = [];

    /**
     * @default 256
     * @param ParamConfigurator|int $value
     * @return $this
     */
    public function maxFilesize($value): static
    {
        $this->_usedProperties['maxFilesize'] = true;
        $this->maxFilesize = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed> $value
     *
     * @return $this
     */
    public function blockedFileTypes(ParamConfigurator|array $value): static
    {
        $this->_usedProperties['blockedFileTypes'] = true;
        $this->blockedFileTypes = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('max_filesize', $config)) {
            $this->_usedProperties['maxFilesize'] = true;
            $this->maxFilesize = $config['max_filesize'];
            unset($config['max_filesize']);
        }

        if (array_key_exists('blocked_file_types', $config)) {
            $this->_usedProperties['blockedFileTypes'] = true;
            $this->blockedFileTypes = $config['blocked_file_types'];
            unset($config['blocked_file_types']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['maxFilesize'])) {
            $output['max_filesize'] = $this->maxFilesize;
        }
        if (isset($this->_usedProperties['blockedFileTypes'])) {
            $output['blocked_file_types'] = $this->blockedFileTypes;
        }

        return $output;
    }

}
