<?php

namespace Symfony\Config\SuluHttpCache;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class CacheConfig 
{
    private $maxAge;
    private $sharedMaxAge;
    private $_usedProperties = [];

    /**
     * @default 240
     * @param ParamConfigurator|int $value
     * @return $this
     */
    public function maxAge($value): static
    {
        $this->_usedProperties['maxAge'] = true;
        $this->maxAge = $value;

        return $this;
    }

    /**
     * @default 240
     * @param ParamConfigurator|int $value
     * @return $this
     */
    public function sharedMaxAge($value): static
    {
        $this->_usedProperties['sharedMaxAge'] = true;
        $this->sharedMaxAge = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('max_age', $config)) {
            $this->_usedProperties['maxAge'] = true;
            $this->maxAge = $config['max_age'];
            unset($config['max_age']);
        }

        if (array_key_exists('shared_max_age', $config)) {
            $this->_usedProperties['sharedMaxAge'] = true;
            $this->sharedMaxAge = $config['shared_max_age'];
            unset($config['shared_max_age']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['maxAge'])) {
            $output['max_age'] = $this->maxAge;
        }
        if (isset($this->_usedProperties['sharedMaxAge'])) {
            $output['shared_max_age'] = $this->sharedMaxAge;
        }

        return $output;
    }

}
