<?php

namespace Symfony\Config\Framework\Mailer;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class DkimSignerConfig 
{
    private $enabled;
    private $key;
    private $domain;
    private $select;
    private $passphrase;
    private $options;
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
     * Key content, or path to key (in PEM format with the `file://` prefix)
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function key($value): static
    {
        $this->_usedProperties['key'] = true;
        $this->key = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function domain($value): static
    {
        $this->_usedProperties['domain'] = true;
        $this->domain = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function select($value): static
    {
        $this->_usedProperties['select'] = true;
        $this->select = $value;

        return $this;
    }

    /**
     * The private key passphrase
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function passphrase($value): static
    {
        $this->_usedProperties['passphrase'] = true;
        $this->passphrase = $value;

        return $this;
    }

    /**
     * @return $this
     */
    public function option(string $name, mixed $value): static
    {
        $this->_usedProperties['options'] = true;
        $this->options[$name] = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('enabled', $config)) {
            $this->_usedProperties['enabled'] = true;
            $this->enabled = $config['enabled'];
            unset($config['enabled']);
        }

        if (array_key_exists('key', $config)) {
            $this->_usedProperties['key'] = true;
            $this->key = $config['key'];
            unset($config['key']);
        }

        if (array_key_exists('domain', $config)) {
            $this->_usedProperties['domain'] = true;
            $this->domain = $config['domain'];
            unset($config['domain']);
        }

        if (array_key_exists('select', $config)) {
            $this->_usedProperties['select'] = true;
            $this->select = $config['select'];
            unset($config['select']);
        }

        if (array_key_exists('passphrase', $config)) {
            $this->_usedProperties['passphrase'] = true;
            $this->passphrase = $config['passphrase'];
            unset($config['passphrase']);
        }

        if (array_key_exists('options', $config)) {
            $this->_usedProperties['options'] = true;
            $this->options = $config['options'];
            unset($config['options']);
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
        if (isset($this->_usedProperties['key'])) {
            $output['key'] = $this->key;
        }
        if (isset($this->_usedProperties['domain'])) {
            $output['domain'] = $this->domain;
        }
        if (isset($this->_usedProperties['select'])) {
            $output['select'] = $this->select;
        }
        if (isset($this->_usedProperties['passphrase'])) {
            $output['passphrase'] = $this->passphrase;
        }
        if (isset($this->_usedProperties['options'])) {
            $output['options'] = $this->options;
        }

        return $output;
    }

}
