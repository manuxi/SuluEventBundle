<?php

namespace Symfony\Config\FosHttpCache;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class CacheManagerConfig 
{
    private $enabled;
    private $customProxyClient;
    private $generateUrlType;
    private $_usedProperties = [];

    /**
     * Allows to disable the invalidation manager. Enabled by default if you configure a proxy client.
     * @default 'auto'
     * @param ParamConfigurator|true|false|'auto' $value
     * @return $this
     */
    public function enabled($value): static
    {
        $this->_usedProperties['enabled'] = true;
        $this->enabled = $value;

        return $this;
    }

    /**
     * Service name of a custom proxy client to use. With a custom client, generate_url_type defaults to ABSOLUTE_URL and tag support needs to be explicitly enabled. If no custom proxy client is specified, the first proxy client you configured is used.
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function customProxyClient($value): static
    {
        $this->_usedProperties['customProxyClient'] = true;
        $this->customProxyClient = $value;

        return $this;
    }

    /**
     * Set what URLs to generate on invalidate/refresh Route. Auto means path if base_url is set on the default proxy client, full URL otherwise.
     * @default 'auto'
     * @param ParamConfigurator|'auto'|1|0|3|2 $value
     * @return $this
     */
    public function generateUrlType($value): static
    {
        $this->_usedProperties['generateUrlType'] = true;
        $this->generateUrlType = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('enabled', $config)) {
            $this->_usedProperties['enabled'] = true;
            $this->enabled = $config['enabled'];
            unset($config['enabled']);
        }

        if (array_key_exists('custom_proxy_client', $config)) {
            $this->_usedProperties['customProxyClient'] = true;
            $this->customProxyClient = $config['custom_proxy_client'];
            unset($config['custom_proxy_client']);
        }

        if (array_key_exists('generate_url_type', $config)) {
            $this->_usedProperties['generateUrlType'] = true;
            $this->generateUrlType = $config['generate_url_type'];
            unset($config['generate_url_type']);
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
        if (isset($this->_usedProperties['customProxyClient'])) {
            $output['custom_proxy_client'] = $this->customProxyClient;
        }
        if (isset($this->_usedProperties['generateUrlType'])) {
            $output['generate_url_type'] = $this->generateUrlType;
        }

        return $output;
    }

}
