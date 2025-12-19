<?php

namespace Symfony\Config\SuluSecurity;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class PasswordPolicyConfig 
{
    private $enabled;
    private $pattern;
    private $infoTranslationKey;
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
     * @default '.{8,}'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function pattern($value): static
    {
        $this->_usedProperties['pattern'] = true;
        $this->pattern = $value;

        return $this;
    }

    /**
     * @default 'sulu_security.password_policy_information'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function infoTranslationKey($value): static
    {
        $this->_usedProperties['infoTranslationKey'] = true;
        $this->infoTranslationKey = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('enabled', $config)) {
            $this->_usedProperties['enabled'] = true;
            $this->enabled = $config['enabled'];
            unset($config['enabled']);
        }

        if (array_key_exists('pattern', $config)) {
            $this->_usedProperties['pattern'] = true;
            $this->pattern = $config['pattern'];
            unset($config['pattern']);
        }

        if (array_key_exists('info_translation_key', $config)) {
            $this->_usedProperties['infoTranslationKey'] = true;
            $this->infoTranslationKey = $config['info_translation_key'];
            unset($config['info_translation_key']);
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
        if (isset($this->_usedProperties['pattern'])) {
            $output['pattern'] = $this->pattern;
        }
        if (isset($this->_usedProperties['infoTranslationKey'])) {
            $output['info_translation_key'] = $this->infoTranslationKey;
        }

        return $output;
    }

}
