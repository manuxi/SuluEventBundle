<?php

namespace Symfony\Config\Security\FirewallConfig\AccessToken\TokenHandler\Oidc;

require_once __DIR__.\DIRECTORY_SEPARATOR.'Discovery'.\DIRECTORY_SEPARATOR.'CacheConfig.php';

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class DiscoveryConfig 
{
    private $baseUri;
    private $cache;
    private $_usedProperties = [];

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed>|string $value
     *
     * @return $this
     */
    public function baseUri(ParamConfigurator|string|array $value): static
    {
        $this->_usedProperties['baseUri'] = true;
        $this->baseUri = $value;

        return $this;
    }

    public function cache(array $value = []): \Symfony\Config\Security\FirewallConfig\AccessToken\TokenHandler\Oidc\Discovery\CacheConfig
    {
        if (null === $this->cache) {
            $this->_usedProperties['cache'] = true;
            $this->cache = new \Symfony\Config\Security\FirewallConfig\AccessToken\TokenHandler\Oidc\Discovery\CacheConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "cache()" has already been initialized. You cannot pass values the second time you call cache().');
        }

        return $this->cache;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('base_uri', $config)) {
            $this->_usedProperties['baseUri'] = true;
            $this->baseUri = $config['base_uri'];
            unset($config['base_uri']);
        }

        if (array_key_exists('cache', $config)) {
            $this->_usedProperties['cache'] = true;
            $this->cache = new \Symfony\Config\Security\FirewallConfig\AccessToken\TokenHandler\Oidc\Discovery\CacheConfig($config['cache']);
            unset($config['cache']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['baseUri'])) {
            $output['base_uri'] = $this->baseUri;
        }
        if (isset($this->_usedProperties['cache'])) {
            $output['cache'] = $this->cache->toArray();
        }

        return $output;
    }

}
