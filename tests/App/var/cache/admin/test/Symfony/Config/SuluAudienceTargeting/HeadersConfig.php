<?php

namespace Symfony\Config\SuluAudienceTargeting;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class HeadersConfig 
{
    private $targetGroup;
    private $url;
    private $_usedProperties = [];

    /**
     * @default 'X-Sulu-Target-Group'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function targetGroup($value): static
    {
        $this->_usedProperties['targetGroup'] = true;
        $this->targetGroup = $value;

        return $this;
    }

    /**
     * @default 'X-Forwarded-URL'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function url($value): static
    {
        $this->_usedProperties['url'] = true;
        $this->url = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('target_group', $config)) {
            $this->_usedProperties['targetGroup'] = true;
            $this->targetGroup = $config['target_group'];
            unset($config['target_group']);
        }

        if (array_key_exists('url', $config)) {
            $this->_usedProperties['url'] = true;
            $this->url = $config['url'];
            unset($config['url']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['targetGroup'])) {
            $output['target_group'] = $this->targetGroup;
        }
        if (isset($this->_usedProperties['url'])) {
            $output['url'] = $this->url;
        }

        return $output;
    }

}
