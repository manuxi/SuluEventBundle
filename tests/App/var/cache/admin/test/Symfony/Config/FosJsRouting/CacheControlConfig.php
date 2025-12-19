<?php

namespace Symfony\Config\FosJsRouting;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class CacheControlConfig 
{
    private $public;
    private $expires;
    private $maxage;
    private $smaxage;
    private $vary;
    private $_usedProperties = [];

    /**
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function public($value): static
    {
        $this->_usedProperties['public'] = true;
        $this->public = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function expires($value): static
    {
        $this->_usedProperties['expires'] = true;
        $this->expires = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function maxage($value): static
    {
        $this->_usedProperties['maxage'] = true;
        $this->maxage = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function smaxage($value): static
    {
        $this->_usedProperties['smaxage'] = true;
        $this->smaxage = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed>|mixed $value
     *
     * @return $this
     */
    public function vary(mixed $value): static
    {
        $this->_usedProperties['vary'] = true;
        $this->vary = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('public', $config)) {
            $this->_usedProperties['public'] = true;
            $this->public = $config['public'];
            unset($config['public']);
        }

        if (array_key_exists('expires', $config)) {
            $this->_usedProperties['expires'] = true;
            $this->expires = $config['expires'];
            unset($config['expires']);
        }

        if (array_key_exists('maxage', $config)) {
            $this->_usedProperties['maxage'] = true;
            $this->maxage = $config['maxage'];
            unset($config['maxage']);
        }

        if (array_key_exists('smaxage', $config)) {
            $this->_usedProperties['smaxage'] = true;
            $this->smaxage = $config['smaxage'];
            unset($config['smaxage']);
        }

        if (array_key_exists('vary', $config)) {
            $this->_usedProperties['vary'] = true;
            $this->vary = $config['vary'];
            unset($config['vary']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['public'])) {
            $output['public'] = $this->public;
        }
        if (isset($this->_usedProperties['expires'])) {
            $output['expires'] = $this->expires;
        }
        if (isset($this->_usedProperties['maxage'])) {
            $output['maxage'] = $this->maxage;
        }
        if (isset($this->_usedProperties['smaxage'])) {
            $output['smaxage'] = $this->smaxage;
        }
        if (isset($this->_usedProperties['vary'])) {
            $output['vary'] = $this->vary;
        }

        return $output;
    }

}
