<?php

namespace Symfony\Config\Security\FirewallConfig\AccessToken\TokenHandler\Oidc;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class EncryptionConfig 
{
    private $enabled;
    private $enforce;
    private $algorithms;
    private $keyset;
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
     * When enabled, the token shall be encrypted.
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function enforce($value): static
    {
        $this->_usedProperties['enforce'] = true;
        $this->enforce = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed> $value
     *
     * @return $this
     */
    public function algorithms(ParamConfigurator|array $value): static
    {
        $this->_usedProperties['algorithms'] = true;
        $this->algorithms = $value;

        return $this;
    }

    /**
     * JSON-encoded JWKSet used to decrypt the token (must contain a list of valid private keys).
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function keyset($value): static
    {
        $this->_usedProperties['keyset'] = true;
        $this->keyset = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('enabled', $config)) {
            $this->_usedProperties['enabled'] = true;
            $this->enabled = $config['enabled'];
            unset($config['enabled']);
        }

        if (array_key_exists('enforce', $config)) {
            $this->_usedProperties['enforce'] = true;
            $this->enforce = $config['enforce'];
            unset($config['enforce']);
        }

        if (array_key_exists('algorithms', $config)) {
            $this->_usedProperties['algorithms'] = true;
            $this->algorithms = $config['algorithms'];
            unset($config['algorithms']);
        }

        if (array_key_exists('keyset', $config)) {
            $this->_usedProperties['keyset'] = true;
            $this->keyset = $config['keyset'];
            unset($config['keyset']);
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
        if (isset($this->_usedProperties['enforce'])) {
            $output['enforce'] = $this->enforce;
        }
        if (isset($this->_usedProperties['algorithms'])) {
            $output['algorithms'] = $this->algorithms;
        }
        if (isset($this->_usedProperties['keyset'])) {
            $output['keyset'] = $this->keyset;
        }

        return $output;
    }

}
