<?php

namespace Symfony\Config\Framework;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class SessionConfig 
{
    private $enabled;
    private $storageFactoryId;
    private $handlerId;
    private $name;
    private $cookieLifetime;
    private $cookiePath;
    private $cookieDomain;
    private $cookieSecure;
    private $cookieHttponly;
    private $cookieSamesite;
    private $useCookies;
    private $gcDivisor;
    private $gcProbability;
    private $gcMaxlifetime;
    private $savePath;
    private $metadataUpdateThreshold;
    private $sidLength;
    private $sidBitsPerCharacter;
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
     * @default 'session.storage.factory.native'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function storageFactoryId($value): static
    {
        $this->_usedProperties['storageFactoryId'] = true;
        $this->storageFactoryId = $value;

        return $this;
    }

    /**
     * Defaults to using the native session handler, or to the native *file* session handler if "save_path" is not null.
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function handlerId($value): static
    {
        $this->_usedProperties['handlerId'] = true;
        $this->handlerId = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function name($value): static
    {
        $this->_usedProperties['name'] = true;
        $this->name = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function cookieLifetime($value): static
    {
        $this->_usedProperties['cookieLifetime'] = true;
        $this->cookieLifetime = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function cookiePath($value): static
    {
        $this->_usedProperties['cookiePath'] = true;
        $this->cookiePath = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function cookieDomain($value): static
    {
        $this->_usedProperties['cookieDomain'] = true;
        $this->cookieDomain = $value;

        return $this;
    }

    /**
     * @default 'auto'
     * @param ParamConfigurator|true|false|'auto' $value
     * @return $this
     */
    public function cookieSecure($value): static
    {
        $this->_usedProperties['cookieSecure'] = true;
        $this->cookieSecure = $value;

        return $this;
    }

    /**
     * @default true
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function cookieHttponly($value): static
    {
        $this->_usedProperties['cookieHttponly'] = true;
        $this->cookieHttponly = $value;

        return $this;
    }

    /**
     * @default 'lax'
     * @param ParamConfigurator|NULL|'lax'|'strict'|'none' $value
     * @return $this
     */
    public function cookieSamesite($value): static
    {
        $this->_usedProperties['cookieSamesite'] = true;
        $this->cookieSamesite = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function useCookies($value): static
    {
        $this->_usedProperties['useCookies'] = true;
        $this->useCookies = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function gcDivisor($value): static
    {
        $this->_usedProperties['gcDivisor'] = true;
        $this->gcDivisor = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function gcProbability($value): static
    {
        $this->_usedProperties['gcProbability'] = true;
        $this->gcProbability = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function gcMaxlifetime($value): static
    {
        $this->_usedProperties['gcMaxlifetime'] = true;
        $this->gcMaxlifetime = $value;

        return $this;
    }

    /**
     * Defaults to "%kernel.cache_dir%/sessions" if the "handler_id" option is not null.
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function savePath($value): static
    {
        $this->_usedProperties['savePath'] = true;
        $this->savePath = $value;

        return $this;
    }

    /**
     * Seconds to wait between 2 session metadata updates.
     * @default 0
     * @param ParamConfigurator|int $value
     * @return $this
     */
    public function metadataUpdateThreshold($value): static
    {
        $this->_usedProperties['metadataUpdateThreshold'] = true;
        $this->metadataUpdateThreshold = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|int $value
     * @deprecated Since symfony/framework-bundle 7.2: Setting the "framework.session.sid_length" configuration option is deprecated. It will be removed in version 8.0. No alternative is provided as PHP 8.4 has deprecated the related option.
     * @return $this
     */
    public function sidLength($value): static
    {
        $this->_usedProperties['sidLength'] = true;
        $this->sidLength = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|int $value
     * @deprecated Since symfony/framework-bundle 7.2: Setting the "framework.session.sid_bits_per_character" configuration option is deprecated. It will be removed in version 8.0. No alternative is provided as PHP 8.4 has deprecated the related option.
     * @return $this
     */
    public function sidBitsPerCharacter($value): static
    {
        $this->_usedProperties['sidBitsPerCharacter'] = true;
        $this->sidBitsPerCharacter = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('enabled', $config)) {
            $this->_usedProperties['enabled'] = true;
            $this->enabled = $config['enabled'];
            unset($config['enabled']);
        }

        if (array_key_exists('storage_factory_id', $config)) {
            $this->_usedProperties['storageFactoryId'] = true;
            $this->storageFactoryId = $config['storage_factory_id'];
            unset($config['storage_factory_id']);
        }

        if (array_key_exists('handler_id', $config)) {
            $this->_usedProperties['handlerId'] = true;
            $this->handlerId = $config['handler_id'];
            unset($config['handler_id']);
        }

        if (array_key_exists('name', $config)) {
            $this->_usedProperties['name'] = true;
            $this->name = $config['name'];
            unset($config['name']);
        }

        if (array_key_exists('cookie_lifetime', $config)) {
            $this->_usedProperties['cookieLifetime'] = true;
            $this->cookieLifetime = $config['cookie_lifetime'];
            unset($config['cookie_lifetime']);
        }

        if (array_key_exists('cookie_path', $config)) {
            $this->_usedProperties['cookiePath'] = true;
            $this->cookiePath = $config['cookie_path'];
            unset($config['cookie_path']);
        }

        if (array_key_exists('cookie_domain', $config)) {
            $this->_usedProperties['cookieDomain'] = true;
            $this->cookieDomain = $config['cookie_domain'];
            unset($config['cookie_domain']);
        }

        if (array_key_exists('cookie_secure', $config)) {
            $this->_usedProperties['cookieSecure'] = true;
            $this->cookieSecure = $config['cookie_secure'];
            unset($config['cookie_secure']);
        }

        if (array_key_exists('cookie_httponly', $config)) {
            $this->_usedProperties['cookieHttponly'] = true;
            $this->cookieHttponly = $config['cookie_httponly'];
            unset($config['cookie_httponly']);
        }

        if (array_key_exists('cookie_samesite', $config)) {
            $this->_usedProperties['cookieSamesite'] = true;
            $this->cookieSamesite = $config['cookie_samesite'];
            unset($config['cookie_samesite']);
        }

        if (array_key_exists('use_cookies', $config)) {
            $this->_usedProperties['useCookies'] = true;
            $this->useCookies = $config['use_cookies'];
            unset($config['use_cookies']);
        }

        if (array_key_exists('gc_divisor', $config)) {
            $this->_usedProperties['gcDivisor'] = true;
            $this->gcDivisor = $config['gc_divisor'];
            unset($config['gc_divisor']);
        }

        if (array_key_exists('gc_probability', $config)) {
            $this->_usedProperties['gcProbability'] = true;
            $this->gcProbability = $config['gc_probability'];
            unset($config['gc_probability']);
        }

        if (array_key_exists('gc_maxlifetime', $config)) {
            $this->_usedProperties['gcMaxlifetime'] = true;
            $this->gcMaxlifetime = $config['gc_maxlifetime'];
            unset($config['gc_maxlifetime']);
        }

        if (array_key_exists('save_path', $config)) {
            $this->_usedProperties['savePath'] = true;
            $this->savePath = $config['save_path'];
            unset($config['save_path']);
        }

        if (array_key_exists('metadata_update_threshold', $config)) {
            $this->_usedProperties['metadataUpdateThreshold'] = true;
            $this->metadataUpdateThreshold = $config['metadata_update_threshold'];
            unset($config['metadata_update_threshold']);
        }

        if (array_key_exists('sid_length', $config)) {
            $this->_usedProperties['sidLength'] = true;
            $this->sidLength = $config['sid_length'];
            unset($config['sid_length']);
        }

        if (array_key_exists('sid_bits_per_character', $config)) {
            $this->_usedProperties['sidBitsPerCharacter'] = true;
            $this->sidBitsPerCharacter = $config['sid_bits_per_character'];
            unset($config['sid_bits_per_character']);
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
        if (isset($this->_usedProperties['storageFactoryId'])) {
            $output['storage_factory_id'] = $this->storageFactoryId;
        }
        if (isset($this->_usedProperties['handlerId'])) {
            $output['handler_id'] = $this->handlerId;
        }
        if (isset($this->_usedProperties['name'])) {
            $output['name'] = $this->name;
        }
        if (isset($this->_usedProperties['cookieLifetime'])) {
            $output['cookie_lifetime'] = $this->cookieLifetime;
        }
        if (isset($this->_usedProperties['cookiePath'])) {
            $output['cookie_path'] = $this->cookiePath;
        }
        if (isset($this->_usedProperties['cookieDomain'])) {
            $output['cookie_domain'] = $this->cookieDomain;
        }
        if (isset($this->_usedProperties['cookieSecure'])) {
            $output['cookie_secure'] = $this->cookieSecure;
        }
        if (isset($this->_usedProperties['cookieHttponly'])) {
            $output['cookie_httponly'] = $this->cookieHttponly;
        }
        if (isset($this->_usedProperties['cookieSamesite'])) {
            $output['cookie_samesite'] = $this->cookieSamesite;
        }
        if (isset($this->_usedProperties['useCookies'])) {
            $output['use_cookies'] = $this->useCookies;
        }
        if (isset($this->_usedProperties['gcDivisor'])) {
            $output['gc_divisor'] = $this->gcDivisor;
        }
        if (isset($this->_usedProperties['gcProbability'])) {
            $output['gc_probability'] = $this->gcProbability;
        }
        if (isset($this->_usedProperties['gcMaxlifetime'])) {
            $output['gc_maxlifetime'] = $this->gcMaxlifetime;
        }
        if (isset($this->_usedProperties['savePath'])) {
            $output['save_path'] = $this->savePath;
        }
        if (isset($this->_usedProperties['metadataUpdateThreshold'])) {
            $output['metadata_update_threshold'] = $this->metadataUpdateThreshold;
        }
        if (isset($this->_usedProperties['sidLength'])) {
            $output['sid_length'] = $this->sidLength;
        }
        if (isset($this->_usedProperties['sidBitsPerCharacter'])) {
            $output['sid_bits_per_character'] = $this->sidBitsPerCharacter;
        }

        return $output;
    }

}
