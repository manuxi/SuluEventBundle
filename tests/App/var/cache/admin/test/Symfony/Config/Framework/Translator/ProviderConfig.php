<?php

namespace Symfony\Config\Framework\Translator;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class ProviderConfig 
{
    private $dsn;
    private $domains;
    private $locales;
    private $_usedProperties = [];

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function dsn($value): static
    {
        $this->_usedProperties['dsn'] = true;
        $this->dsn = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed> $value
     *
     * @return $this
     */
    public function domains(ParamConfigurator|array $value): static
    {
        $this->_usedProperties['domains'] = true;
        $this->domains = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed> $value
     *
     * @return $this
     */
    public function locales(ParamConfigurator|array $value): static
    {
        $this->_usedProperties['locales'] = true;
        $this->locales = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('dsn', $config)) {
            $this->_usedProperties['dsn'] = true;
            $this->dsn = $config['dsn'];
            unset($config['dsn']);
        }

        if (array_key_exists('domains', $config)) {
            $this->_usedProperties['domains'] = true;
            $this->domains = $config['domains'];
            unset($config['domains']);
        }

        if (array_key_exists('locales', $config)) {
            $this->_usedProperties['locales'] = true;
            $this->locales = $config['locales'];
            unset($config['locales']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['dsn'])) {
            $output['dsn'] = $this->dsn;
        }
        if (isset($this->_usedProperties['domains'])) {
            $output['domains'] = $this->domains;
        }
        if (isset($this->_usedProperties['locales'])) {
            $output['locales'] = $this->locales;
        }

        return $output;
    }

}
