<?php

namespace Symfony\Config\Framework;

require_once __DIR__.\DIRECTORY_SEPARATOR.'Validation'.\DIRECTORY_SEPARATOR.'MappingConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Validation'.\DIRECTORY_SEPARATOR.'NotCompromisedPasswordConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Validation'.\DIRECTORY_SEPARATOR.'AutoMappingConfig.php';

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class ValidationConfig 
{
    private $enabled;
    private $cache;
    private $enableAttributes;
    private $staticMethod;
    private $translationDomain;
    private $emailValidationMode;
    private $mapping;
    private $notCompromisedPassword;
    private $disableTranslation;
    private $autoMapping;
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
     * @default null
     * @param ParamConfigurator|mixed $value
     * @deprecated Since symfony/framework-bundle 7.3: Setting the "framework.validation.cache" configuration option is deprecated. It will be removed in version 8.0.
     * @return $this
     */
    public function cache($value): static
    {
        $this->_usedProperties['cache'] = true;
        $this->cache = $value;

        return $this;
    }

    /**
     * @default true
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function enableAttributes($value): static
    {
        $this->_usedProperties['enableAttributes'] = true;
        $this->enableAttributes = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed> $value
     *
     * @return $this
     */
    public function staticMethod(ParamConfigurator|array $value): static
    {
        $this->_usedProperties['staticMethod'] = true;
        $this->staticMethod = $value;

        return $this;
    }

    /**
     * @default 'validators'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function translationDomain($value): static
    {
        $this->_usedProperties['translationDomain'] = true;
        $this->translationDomain = $value;

        return $this;
    }

    /**
     * @default 'html5'
     * @param ParamConfigurator|'html5'|'html5-allow-no-tld'|'strict'|'loose' $value
     * @return $this
     */
    public function emailValidationMode($value): static
    {
        $this->_usedProperties['emailValidationMode'] = true;
        $this->emailValidationMode = $value;

        return $this;
    }

    /**
     * @default {"paths":[]}
     */
    public function mapping(array $value = []): \Symfony\Config\Framework\Validation\MappingConfig
    {
        if (null === $this->mapping) {
            $this->_usedProperties['mapping'] = true;
            $this->mapping = new \Symfony\Config\Framework\Validation\MappingConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "mapping()" has already been initialized. You cannot pass values the second time you call mapping().');
        }

        return $this->mapping;
    }

    /**
     * @template TValue of array|bool
     * @param TValue $value
     * @default {"enabled":true,"endpoint":null}
     * @return \Symfony\Config\Framework\Validation\NotCompromisedPasswordConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\Framework\Validation\NotCompromisedPasswordConfig : static)
     */
    public function notCompromisedPassword(array|bool $value = []): \Symfony\Config\Framework\Validation\NotCompromisedPasswordConfig|static
    {
        if (!\is_array($value)) {
            $this->_usedProperties['notCompromisedPassword'] = true;
            $this->notCompromisedPassword = $value;

            return $this;
        }

        if (!$this->notCompromisedPassword instanceof \Symfony\Config\Framework\Validation\NotCompromisedPasswordConfig) {
            $this->_usedProperties['notCompromisedPassword'] = true;
            $this->notCompromisedPassword = new \Symfony\Config\Framework\Validation\NotCompromisedPasswordConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "notCompromisedPassword()" has already been initialized. You cannot pass values the second time you call notCompromisedPassword().');
        }

        return $this->notCompromisedPassword;
    }

    /**
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function disableTranslation($value): static
    {
        $this->_usedProperties['disableTranslation'] = true;
        $this->disableTranslation = $value;

        return $this;
    }

    /**
     * A collection of namespaces for which auto-mapping will be enabled by default, or null to opt-in with the EnableAutoMapping constraint.
     * @example []
     * @example ["validator.property_info_loader"]
     */
    public function autoMapping(string $namespace, array $value = []): \Symfony\Config\Framework\Validation\AutoMappingConfig
    {
        if (!isset($this->autoMapping[$namespace])) {
            $this->_usedProperties['autoMapping'] = true;
            $this->autoMapping[$namespace] = new \Symfony\Config\Framework\Validation\AutoMappingConfig($value);
        } elseif (1 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "autoMapping()" has already been initialized. You cannot pass values the second time you call autoMapping().');
        }

        return $this->autoMapping[$namespace];
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('enabled', $config)) {
            $this->_usedProperties['enabled'] = true;
            $this->enabled = $config['enabled'];
            unset($config['enabled']);
        }

        if (array_key_exists('cache', $config)) {
            $this->_usedProperties['cache'] = true;
            $this->cache = $config['cache'];
            unset($config['cache']);
        }

        if (array_key_exists('enable_attributes', $config)) {
            $this->_usedProperties['enableAttributes'] = true;
            $this->enableAttributes = $config['enable_attributes'];
            unset($config['enable_attributes']);
        }

        if (array_key_exists('static_method', $config)) {
            $this->_usedProperties['staticMethod'] = true;
            $this->staticMethod = $config['static_method'];
            unset($config['static_method']);
        }

        if (array_key_exists('translation_domain', $config)) {
            $this->_usedProperties['translationDomain'] = true;
            $this->translationDomain = $config['translation_domain'];
            unset($config['translation_domain']);
        }

        if (array_key_exists('email_validation_mode', $config)) {
            $this->_usedProperties['emailValidationMode'] = true;
            $this->emailValidationMode = $config['email_validation_mode'];
            unset($config['email_validation_mode']);
        }

        if (array_key_exists('mapping', $config)) {
            $this->_usedProperties['mapping'] = true;
            $this->mapping = new \Symfony\Config\Framework\Validation\MappingConfig($config['mapping']);
            unset($config['mapping']);
        }

        if (array_key_exists('not_compromised_password', $config)) {
            $this->_usedProperties['notCompromisedPassword'] = true;
            $this->notCompromisedPassword = \is_array($config['not_compromised_password']) ? new \Symfony\Config\Framework\Validation\NotCompromisedPasswordConfig($config['not_compromised_password']) : $config['not_compromised_password'];
            unset($config['not_compromised_password']);
        }

        if (array_key_exists('disable_translation', $config)) {
            $this->_usedProperties['disableTranslation'] = true;
            $this->disableTranslation = $config['disable_translation'];
            unset($config['disable_translation']);
        }

        if (array_key_exists('auto_mapping', $config)) {
            $this->_usedProperties['autoMapping'] = true;
            $this->autoMapping = array_map(fn ($v) => new \Symfony\Config\Framework\Validation\AutoMappingConfig($v), $config['auto_mapping']);
            unset($config['auto_mapping']);
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
        if (isset($this->_usedProperties['cache'])) {
            $output['cache'] = $this->cache;
        }
        if (isset($this->_usedProperties['enableAttributes'])) {
            $output['enable_attributes'] = $this->enableAttributes;
        }
        if (isset($this->_usedProperties['staticMethod'])) {
            $output['static_method'] = $this->staticMethod;
        }
        if (isset($this->_usedProperties['translationDomain'])) {
            $output['translation_domain'] = $this->translationDomain;
        }
        if (isset($this->_usedProperties['emailValidationMode'])) {
            $output['email_validation_mode'] = $this->emailValidationMode;
        }
        if (isset($this->_usedProperties['mapping'])) {
            $output['mapping'] = $this->mapping->toArray();
        }
        if (isset($this->_usedProperties['notCompromisedPassword'])) {
            $output['not_compromised_password'] = $this->notCompromisedPassword instanceof \Symfony\Config\Framework\Validation\NotCompromisedPasswordConfig ? $this->notCompromisedPassword->toArray() : $this->notCompromisedPassword;
        }
        if (isset($this->_usedProperties['disableTranslation'])) {
            $output['disable_translation'] = $this->disableTranslation;
        }
        if (isset($this->_usedProperties['autoMapping'])) {
            $output['auto_mapping'] = array_map(fn ($v) => $v->toArray(), $this->autoMapping);
        }

        return $output;
    }

}
