<?php

namespace Symfony\Config\SuluWebsite\Twig;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class ContentConfig 
{
    private $cacheLifetime;
    private $_usedProperties = [];

    /**
     * @default 1
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function cacheLifetime($value): static
    {
        $this->_usedProperties['cacheLifetime'] = true;
        $this->cacheLifetime = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('cache_lifetime', $config)) {
            $this->_usedProperties['cacheLifetime'] = true;
            $this->cacheLifetime = $config['cache_lifetime'];
            unset($config['cache_lifetime']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['cacheLifetime'])) {
            $output['cache_lifetime'] = $this->cacheLifetime;
        }

        return $output;
    }

}
