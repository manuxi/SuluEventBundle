<?php

namespace Symfony\Config\Framework\Translator;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class PseudoLocalizationConfig 
{
    private $enabled;
    private $accents;
    private $expansionFactor;
    private $brackets;
    private $parseHtml;
    private $localizableHtmlAttributes;
    private $_usedProperties = [];

    /**
     * @default false
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
     * @default true
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function accents($value): static
    {
        $this->_usedProperties['accents'] = true;
        $this->accents = $value;

        return $this;
    }

    /**
     * @default 1.0
     * @param ParamConfigurator|float $value
     * @return $this
     */
    public function expansionFactor($value): static
    {
        $this->_usedProperties['expansionFactor'] = true;
        $this->expansionFactor = $value;

        return $this;
    }

    /**
     * @default true
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function brackets($value): static
    {
        $this->_usedProperties['brackets'] = true;
        $this->brackets = $value;

        return $this;
    }

    /**
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function parseHtml($value): static
    {
        $this->_usedProperties['parseHtml'] = true;
        $this->parseHtml = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed> $value
     *
     * @return $this
     */
    public function localizableHtmlAttributes(ParamConfigurator|array $value): static
    {
        $this->_usedProperties['localizableHtmlAttributes'] = true;
        $this->localizableHtmlAttributes = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('enabled', $config)) {
            $this->_usedProperties['enabled'] = true;
            $this->enabled = $config['enabled'];
            unset($config['enabled']);
        }

        if (array_key_exists('accents', $config)) {
            $this->_usedProperties['accents'] = true;
            $this->accents = $config['accents'];
            unset($config['accents']);
        }

        if (array_key_exists('expansion_factor', $config)) {
            $this->_usedProperties['expansionFactor'] = true;
            $this->expansionFactor = $config['expansion_factor'];
            unset($config['expansion_factor']);
        }

        if (array_key_exists('brackets', $config)) {
            $this->_usedProperties['brackets'] = true;
            $this->brackets = $config['brackets'];
            unset($config['brackets']);
        }

        if (array_key_exists('parse_html', $config)) {
            $this->_usedProperties['parseHtml'] = true;
            $this->parseHtml = $config['parse_html'];
            unset($config['parse_html']);
        }

        if (array_key_exists('localizable_html_attributes', $config)) {
            $this->_usedProperties['localizableHtmlAttributes'] = true;
            $this->localizableHtmlAttributes = $config['localizable_html_attributes'];
            unset($config['localizable_html_attributes']);
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
        if (isset($this->_usedProperties['accents'])) {
            $output['accents'] = $this->accents;
        }
        if (isset($this->_usedProperties['expansionFactor'])) {
            $output['expansion_factor'] = $this->expansionFactor;
        }
        if (isset($this->_usedProperties['brackets'])) {
            $output['brackets'] = $this->brackets;
        }
        if (isset($this->_usedProperties['parseHtml'])) {
            $output['parse_html'] = $this->parseHtml;
        }
        if (isset($this->_usedProperties['localizableHtmlAttributes'])) {
            $output['localizable_html_attributes'] = $this->localizableHtmlAttributes;
        }

        return $output;
    }

}
