<?php

namespace Symfony\Config\FosHttpCache\ProxyClient;

require_once __DIR__.\DIRECTORY_SEPARATOR.'Varnish'.\DIRECTORY_SEPARATOR.'HttpConfig.php';

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class VarnishConfig 
{
    private $tagsHeader;
    private $headerLength;
    private $defaultBanHeaders;
    private $tagMode;
    private $http;
    private $_usedProperties = [];

    /**
     * HTTP header to use when sending tag invalidation requests to Varnish
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function tagsHeader($value): static
    {
        $this->_usedProperties['tagsHeader'] = true;
        $this->tagsHeader = $value;

        return $this;
    }

    /**
     * Maximum header length when invalidating tags. If there are more tags to invalidate than fit into the header, the invalidation request is split into several requests.
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function headerLength($value): static
    {
        $this->_usedProperties['headerLength'] = true;
        $this->headerLength = $value;

        return $this;
    }

    /**
     * @return $this
     */
    public function defaultBanHeader(string $name, mixed $value): static
    {
        $this->_usedProperties['defaultBanHeaders'] = true;
        $this->defaultBanHeaders[$name] = $value;

        return $this;
    }

    /**
     * If you can enable the xkey module in Varnish, use the purgekeys mode for more efficient tag handling
     * @default 'ban'
     * @param ParamConfigurator|'ban'|'purgekeys' $value
     * @return $this
     */
    public function tagMode($value): static
    {
        $this->_usedProperties['tagMode'] = true;
        $this->tagMode = $value;

        return $this;
    }

    public function http(array $value = []): \Symfony\Config\FosHttpCache\ProxyClient\Varnish\HttpConfig
    {
        if (null === $this->http) {
            $this->_usedProperties['http'] = true;
            $this->http = new \Symfony\Config\FosHttpCache\ProxyClient\Varnish\HttpConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "http()" has already been initialized. You cannot pass values the second time you call http().');
        }

        return $this->http;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('tags_header', $config)) {
            $this->_usedProperties['tagsHeader'] = true;
            $this->tagsHeader = $config['tags_header'];
            unset($config['tags_header']);
        }

        if (array_key_exists('header_length', $config)) {
            $this->_usedProperties['headerLength'] = true;
            $this->headerLength = $config['header_length'];
            unset($config['header_length']);
        }

        if (array_key_exists('default_ban_headers', $config)) {
            $this->_usedProperties['defaultBanHeaders'] = true;
            $this->defaultBanHeaders = $config['default_ban_headers'];
            unset($config['default_ban_headers']);
        }

        if (array_key_exists('tag_mode', $config)) {
            $this->_usedProperties['tagMode'] = true;
            $this->tagMode = $config['tag_mode'];
            unset($config['tag_mode']);
        }

        if (array_key_exists('http', $config)) {
            $this->_usedProperties['http'] = true;
            $this->http = new \Symfony\Config\FosHttpCache\ProxyClient\Varnish\HttpConfig($config['http']);
            unset($config['http']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['tagsHeader'])) {
            $output['tags_header'] = $this->tagsHeader;
        }
        if (isset($this->_usedProperties['headerLength'])) {
            $output['header_length'] = $this->headerLength;
        }
        if (isset($this->_usedProperties['defaultBanHeaders'])) {
            $output['default_ban_headers'] = $this->defaultBanHeaders;
        }
        if (isset($this->_usedProperties['tagMode'])) {
            $output['tag_mode'] = $this->tagMode;
        }
        if (isset($this->_usedProperties['http'])) {
            $output['http'] = $this->http->toArray();
        }

        return $output;
    }

}
