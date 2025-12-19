<?php

namespace Symfony\Config\Framework\HttpClient\ScopedClientConfig;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class PeerFingerprintConfig 
{
    private $sha1;
    private $pinsha256;
    private $md5;
    private $_usedProperties = [];

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     *
     * @return $this
     */
    public function sha1(mixed $value): static
    {
        $this->_usedProperties['sha1'] = true;
        $this->sha1 = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     *
     * @return $this
     */
    public function pinsha256(mixed $value): static
    {
        $this->_usedProperties['pinsha256'] = true;
        $this->pinsha256 = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     *
     * @return $this
     */
    public function md5(mixed $value): static
    {
        $this->_usedProperties['md5'] = true;
        $this->md5 = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('sha1', $config)) {
            $this->_usedProperties['sha1'] = true;
            $this->sha1 = $config['sha1'];
            unset($config['sha1']);
        }

        if (array_key_exists('pin-sha256', $config)) {
            $this->_usedProperties['pinsha256'] = true;
            $this->pinsha256 = $config['pin-sha256'];
            unset($config['pin-sha256']);
        }

        if (array_key_exists('md5', $config)) {
            $this->_usedProperties['md5'] = true;
            $this->md5 = $config['md5'];
            unset($config['md5']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['sha1'])) {
            $output['sha1'] = $this->sha1;
        }
        if (isset($this->_usedProperties['pinsha256'])) {
            $output['pin-sha256'] = $this->pinsha256;
        }
        if (isset($this->_usedProperties['md5'])) {
            $output['md5'] = $this->md5;
        }

        return $output;
    }

}
