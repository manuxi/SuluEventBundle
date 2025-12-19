<?php

namespace Symfony\Config\SuluAudienceTargeting;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class CookiesConfig 
{
    private $targetGroup;
    private $session;
    private $_usedProperties = [];

    /**
     * @default '_svtg'
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
     * @default '_svs'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function session($value): static
    {
        $this->_usedProperties['session'] = true;
        $this->session = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('target_group', $config)) {
            $this->_usedProperties['targetGroup'] = true;
            $this->targetGroup = $config['target_group'];
            unset($config['target_group']);
        }

        if (array_key_exists('session', $config)) {
            $this->_usedProperties['session'] = true;
            $this->session = $config['session'];
            unset($config['session']);
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
        if (isset($this->_usedProperties['session'])) {
            $output['session'] = $this->session;
        }

        return $output;
    }

}
