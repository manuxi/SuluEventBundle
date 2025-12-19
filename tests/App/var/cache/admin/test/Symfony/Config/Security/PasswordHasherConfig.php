<?php

namespace Symfony\Config\Security;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class PasswordHasherConfig 
{
    private $algorithm;
    private $migrateFrom;
    private $hashAlgorithm;
    private $keyLength;
    private $ignoreCase;
    private $encodeAsBase64;
    private $iterations;
    private $cost;
    private $memoryCost;
    private $timeCost;
    private $id;
    private $_usedProperties = [];

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function algorithm($value): static
    {
        $this->_usedProperties['algorithm'] = true;
        $this->algorithm = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed>|string $value
     *
     * @return $this
     */
    public function migrateFrom(ParamConfigurator|string|array $value): static
    {
        $this->_usedProperties['migrateFrom'] = true;
        $this->migrateFrom = $value;

        return $this;
    }

    /**
     * Name of hashing algorithm for PBKDF2 (i.e. sha256, sha512, etc..) See hash_algos() for a list of supported algorithms.
     * @default 'sha512'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function hashAlgorithm($value): static
    {
        $this->_usedProperties['hashAlgorithm'] = true;
        $this->hashAlgorithm = $value;

        return $this;
    }

    /**
     * @default 40
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function keyLength($value): static
    {
        $this->_usedProperties['keyLength'] = true;
        $this->keyLength = $value;

        return $this;
    }

    /**
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function ignoreCase($value): static
    {
        $this->_usedProperties['ignoreCase'] = true;
        $this->ignoreCase = $value;

        return $this;
    }

    /**
     * @default true
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function encodeAsBase64($value): static
    {
        $this->_usedProperties['encodeAsBase64'] = true;
        $this->encodeAsBase64 = $value;

        return $this;
    }

    /**
     * @default 5000
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function iterations($value): static
    {
        $this->_usedProperties['iterations'] = true;
        $this->iterations = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|int $value
     * @return $this
     */
    public function cost($value): static
    {
        $this->_usedProperties['cost'] = true;
        $this->cost = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function memoryCost($value): static
    {
        $this->_usedProperties['memoryCost'] = true;
        $this->memoryCost = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function timeCost($value): static
    {
        $this->_usedProperties['timeCost'] = true;
        $this->timeCost = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function id($value): static
    {
        $this->_usedProperties['id'] = true;
        $this->id = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('algorithm', $config)) {
            $this->_usedProperties['algorithm'] = true;
            $this->algorithm = $config['algorithm'];
            unset($config['algorithm']);
        }

        if (array_key_exists('migrate_from', $config)) {
            $this->_usedProperties['migrateFrom'] = true;
            $this->migrateFrom = $config['migrate_from'];
            unset($config['migrate_from']);
        }

        if (array_key_exists('hash_algorithm', $config)) {
            $this->_usedProperties['hashAlgorithm'] = true;
            $this->hashAlgorithm = $config['hash_algorithm'];
            unset($config['hash_algorithm']);
        }

        if (array_key_exists('key_length', $config)) {
            $this->_usedProperties['keyLength'] = true;
            $this->keyLength = $config['key_length'];
            unset($config['key_length']);
        }

        if (array_key_exists('ignore_case', $config)) {
            $this->_usedProperties['ignoreCase'] = true;
            $this->ignoreCase = $config['ignore_case'];
            unset($config['ignore_case']);
        }

        if (array_key_exists('encode_as_base64', $config)) {
            $this->_usedProperties['encodeAsBase64'] = true;
            $this->encodeAsBase64 = $config['encode_as_base64'];
            unset($config['encode_as_base64']);
        }

        if (array_key_exists('iterations', $config)) {
            $this->_usedProperties['iterations'] = true;
            $this->iterations = $config['iterations'];
            unset($config['iterations']);
        }

        if (array_key_exists('cost', $config)) {
            $this->_usedProperties['cost'] = true;
            $this->cost = $config['cost'];
            unset($config['cost']);
        }

        if (array_key_exists('memory_cost', $config)) {
            $this->_usedProperties['memoryCost'] = true;
            $this->memoryCost = $config['memory_cost'];
            unset($config['memory_cost']);
        }

        if (array_key_exists('time_cost', $config)) {
            $this->_usedProperties['timeCost'] = true;
            $this->timeCost = $config['time_cost'];
            unset($config['time_cost']);
        }

        if (array_key_exists('id', $config)) {
            $this->_usedProperties['id'] = true;
            $this->id = $config['id'];
            unset($config['id']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['algorithm'])) {
            $output['algorithm'] = $this->algorithm;
        }
        if (isset($this->_usedProperties['migrateFrom'])) {
            $output['migrate_from'] = $this->migrateFrom;
        }
        if (isset($this->_usedProperties['hashAlgorithm'])) {
            $output['hash_algorithm'] = $this->hashAlgorithm;
        }
        if (isset($this->_usedProperties['keyLength'])) {
            $output['key_length'] = $this->keyLength;
        }
        if (isset($this->_usedProperties['ignoreCase'])) {
            $output['ignore_case'] = $this->ignoreCase;
        }
        if (isset($this->_usedProperties['encodeAsBase64'])) {
            $output['encode_as_base64'] = $this->encodeAsBase64;
        }
        if (isset($this->_usedProperties['iterations'])) {
            $output['iterations'] = $this->iterations;
        }
        if (isset($this->_usedProperties['cost'])) {
            $output['cost'] = $this->cost;
        }
        if (isset($this->_usedProperties['memoryCost'])) {
            $output['memory_cost'] = $this->memoryCost;
        }
        if (isset($this->_usedProperties['timeCost'])) {
            $output['time_cost'] = $this->timeCost;
        }
        if (isset($this->_usedProperties['id'])) {
            $output['id'] = $this->id;
        }

        return $output;
    }

}
