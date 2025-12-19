<?php

namespace Symfony\Config\FosHttpCache\Test;

require_once __DIR__.\DIRECTORY_SEPARATOR.'ProxyServer'.\DIRECTORY_SEPARATOR.'VarnishConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'ProxyServer'.\DIRECTORY_SEPARATOR.'NginxConfig.php';

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class ProxyServerConfig 
{
    private $default;
    private $varnish;
    private $nginx;
    private $_usedProperties = [];

    /**
     * If you configure more than one proxy server, specify which client is the default.
     * @default null
     * @param ParamConfigurator|'varnish'|'nginx' $value
     * @return $this
     */
    public function default($value): static
    {
        $this->_usedProperties['default'] = true;
        $this->default = $value;

        return $this;
    }

    public function varnish(array $value = []): \Symfony\Config\FosHttpCache\Test\ProxyServer\VarnishConfig
    {
        if (null === $this->varnish) {
            $this->_usedProperties['varnish'] = true;
            $this->varnish = new \Symfony\Config\FosHttpCache\Test\ProxyServer\VarnishConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "varnish()" has already been initialized. You cannot pass values the second time you call varnish().');
        }

        return $this->varnish;
    }

    public function nginx(array $value = []): \Symfony\Config\FosHttpCache\Test\ProxyServer\NginxConfig
    {
        if (null === $this->nginx) {
            $this->_usedProperties['nginx'] = true;
            $this->nginx = new \Symfony\Config\FosHttpCache\Test\ProxyServer\NginxConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "nginx()" has already been initialized. You cannot pass values the second time you call nginx().');
        }

        return $this->nginx;
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
            $this->varnish = new \Symfony\Config\FosHttpCache\Test\ProxyServer\VarnishConfig($config['varnish']);
            unset($config['varnish']);
        }

        if (array_key_exists('nginx', $config)) {
            $this->_usedProperties['nginx'] = true;
            $this->nginx = new \Symfony\Config\FosHttpCache\Test\ProxyServer\NginxConfig($config['nginx']);
            unset($config['nginx']);
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

        return $output;
    }

}
