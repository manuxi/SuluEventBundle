<?php

namespace Symfony\Config\FosRest\Versioning\Resolvers;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class MediaTypeConfig 
{
    private $enabled;
    private $regex;
    private $_usedProperties = [];

    /**
     * @default true
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
     * @default '/(v|version)=(?P<version>[0-9\\.]+)/'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function regex($value): static
    {
        $this->_usedProperties['regex'] = true;
        $this->regex = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('enabled', $config)) {
            $this->_usedProperties['enabled'] = true;
            $this->enabled = $config['enabled'];
            unset($config['enabled']);
        }

        if (array_key_exists('regex', $config)) {
            $this->_usedProperties['regex'] = true;
            $this->regex = $config['regex'];
            unset($config['regex']);
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
        if (isset($this->_usedProperties['regex'])) {
            $output['regex'] = $this->regex;
        }

        return $output;
    }

}
