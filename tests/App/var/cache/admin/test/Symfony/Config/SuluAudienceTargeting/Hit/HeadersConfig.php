<?php

namespace Symfony\Config\SuluAudienceTargeting\Hit;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class HeadersConfig 
{
    private $referrer;
    private $uuid;
    private $_usedProperties = [];

    /**
     * @default 'X-Forwarded-Referrer'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function referrer($value): static
    {
        $this->_usedProperties['referrer'] = true;
        $this->referrer = $value;

        return $this;
    }

    /**
     * @default 'X-Forwarded-UUID'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function uuid($value): static
    {
        $this->_usedProperties['uuid'] = true;
        $this->uuid = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('referrer', $config)) {
            $this->_usedProperties['referrer'] = true;
            $this->referrer = $config['referrer'];
            unset($config['referrer']);
        }

        if (array_key_exists('uuid', $config)) {
            $this->_usedProperties['uuid'] = true;
            $this->uuid = $config['uuid'];
            unset($config['uuid']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['referrer'])) {
            $output['referrer'] = $this->referrer;
        }
        if (isset($this->_usedProperties['uuid'])) {
            $output['uuid'] = $this->uuid;
        }

        return $output;
    }

}
