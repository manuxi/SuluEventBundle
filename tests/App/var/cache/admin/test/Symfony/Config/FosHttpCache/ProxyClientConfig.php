<?php

namespace Symfony\Config\FosHttpCache;

require_once __DIR__.\DIRECTORY_SEPARATOR.'ProxyClient'.\DIRECTORY_SEPARATOR.'VarnishConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'ProxyClient'.\DIRECTORY_SEPARATOR.'NginxConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'ProxyClient'.\DIRECTORY_SEPARATOR.'SymfonyConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'ProxyClient'.\DIRECTORY_SEPARATOR.'CloudflareConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'ProxyClient'.\DIRECTORY_SEPARATOR.'CloudfrontConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'ProxyClient'.\DIRECTORY_SEPARATOR.'FastlyConfig.php';

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class ProxyClientConfig 
{
    private $default;
    private $varnish;
    private $nginx;
    private $symfony;
    private $cloudflare;
    private $cloudfront;
    private $fastly;
    private $noop;
    private $_usedProperties = [];

    /**
     * If you configure more than one proxy client, you need to specify which client is the default.
     * @default null
     * @param ParamConfigurator|'varnish'|'nginx'|'symfony'|'cloudflare'|'cloudfront'|'fastly'|'noop' $value
     * @return $this
     */
    public function default($value): static
    {
        $this->_usedProperties['default'] = true;
        $this->default = $value;

        return $this;
    }

    public function varnish(array $value = []): \Symfony\Config\FosHttpCache\ProxyClient\VarnishConfig
    {
        if (null === $this->varnish) {
            $this->_usedProperties['varnish'] = true;
            $this->varnish = new \Symfony\Config\FosHttpCache\ProxyClient\VarnishConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "varnish()" has already been initialized. You cannot pass values the second time you call varnish().');
        }

        return $this->varnish;
    }

    public function nginx(array $value = []): \Symfony\Config\FosHttpCache\ProxyClient\NginxConfig
    {
        if (null === $this->nginx) {
            $this->_usedProperties['nginx'] = true;
            $this->nginx = new \Symfony\Config\FosHttpCache\ProxyClient\NginxConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "nginx()" has already been initialized. You cannot pass values the second time you call nginx().');
        }

        return $this->nginx;
    }

    public function symfony(array $value = []): \Symfony\Config\FosHttpCache\ProxyClient\SymfonyConfig
    {
        if (null === $this->symfony) {
            $this->_usedProperties['symfony'] = true;
            $this->symfony = new \Symfony\Config\FosHttpCache\ProxyClient\SymfonyConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "symfony()" has already been initialized. You cannot pass values the second time you call symfony().');
        }

        return $this->symfony;
    }

    public function cloudflare(array $value = []): \Symfony\Config\FosHttpCache\ProxyClient\CloudflareConfig
    {
        if (null === $this->cloudflare) {
            $this->_usedProperties['cloudflare'] = true;
            $this->cloudflare = new \Symfony\Config\FosHttpCache\ProxyClient\CloudflareConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "cloudflare()" has already been initialized. You cannot pass values the second time you call cloudflare().');
        }

        return $this->cloudflare;
    }

    /**
     * Configure a client to interact with AWS cloudfront. You need to install jean-beru/fos-http-cache-cloudfront to work with cloudfront
     */
    public function cloudfront(array $value = []): \Symfony\Config\FosHttpCache\ProxyClient\CloudfrontConfig
    {
        if (null === $this->cloudfront) {
            $this->_usedProperties['cloudfront'] = true;
            $this->cloudfront = new \Symfony\Config\FosHttpCache\ProxyClient\CloudfrontConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "cloudfront()" has already been initialized. You cannot pass values the second time you call cloudfront().');
        }

        return $this->cloudfront;
    }

    /**
     * Configure a client to interact with Fastly.
     */
    public function fastly(array $value = []): \Symfony\Config\FosHttpCache\ProxyClient\FastlyConfig
    {
        if (null === $this->fastly) {
            $this->_usedProperties['fastly'] = true;
            $this->fastly = new \Symfony\Config\FosHttpCache\ProxyClient\FastlyConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "fastly()" has already been initialized. You cannot pass values the second time you call fastly().');
        }

        return $this->fastly;
    }

    /**
     * @default null
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function noop($value): static
    {
        $this->_usedProperties['noop'] = true;
        $this->noop = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('default', $config)) {
            $this->_usedProperties['default'] = true;
            $this->default = $config['default'];
            unset($config['default']);
        }

        if (array_key_exists('varnish', $config)) {
            $this->_usedProperties['varnish'] = true;
            $this->varnish = new \Symfony\Config\FosHttpCache\ProxyClient\VarnishConfig($config['varnish']);
            unset($config['varnish']);
        }

        if (array_key_exists('nginx', $config)) {
            $this->_usedProperties['nginx'] = true;
            $this->nginx = new \Symfony\Config\FosHttpCache\ProxyClient\NginxConfig($config['nginx']);
            unset($config['nginx']);
        }

        if (array_key_exists('symfony', $config)) {
            $this->_usedProperties['symfony'] = true;
            $this->symfony = new \Symfony\Config\FosHttpCache\ProxyClient\SymfonyConfig($config['symfony']);
            unset($config['symfony']);
        }

        if (array_key_exists('cloudflare', $config)) {
            $this->_usedProperties['cloudflare'] = true;
            $this->cloudflare = new \Symfony\Config\FosHttpCache\ProxyClient\CloudflareConfig($config['cloudflare']);
            unset($config['cloudflare']);
        }

        if (array_key_exists('cloudfront', $config)) {
            $this->_usedProperties['cloudfront'] = true;
            $this->cloudfront = new \Symfony\Config\FosHttpCache\ProxyClient\CloudfrontConfig($config['cloudfront']);
            unset($config['cloudfront']);
        }

        if (array_key_exists('fastly', $config)) {
            $this->_usedProperties['fastly'] = true;
            $this->fastly = new \Symfony\Config\FosHttpCache\ProxyClient\FastlyConfig($config['fastly']);
            unset($config['fastly']);
        }

        if (array_key_exists('noop', $config)) {
            $this->_usedProperties['noop'] = true;
            $this->noop = $config['noop'];
            unset($config['noop']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['default'])) {
            $output['default'] = $this->default;
        }
        if (isset($this->_usedProperties['varnish'])) {
            $output['varnish'] = $this->varnish->toArray();
        }
        if (isset($this->_usedProperties['nginx'])) {
            $output['nginx'] = $this->nginx->toArray();
        }
        if (isset($this->_usedProperties['symfony'])) {
            $output['symfony'] = $this->symfony->toArray();
        }
        if (isset($this->_usedProperties['cloudflare'])) {
            $output['cloudflare'] = $this->cloudflare->toArray();
        }
        if (isset($this->_usedProperties['cloudfront'])) {
            $output['cloudfront'] = $this->cloudfront->toArray();
        }
        if (isset($this->_usedProperties['fastly'])) {
            $output['fastly'] = $this->fastly->toArray();
        }
        if (isset($this->_usedProperties['noop'])) {
            $output['noop'] = $this->noop;
        }

        return $output;
    }

}
