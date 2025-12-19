<?php

namespace Symfony\Config;

require_once __DIR__.\DIRECTORY_SEPARATOR.'FosJsRouting'.\DIRECTORY_SEPARATOR.'CacheControlConfig.php';

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class FosJsRoutingConfig implements \Symfony\Component\Config\Builder\ConfigBuilderInterface
{
    private $serializer;
    private $routesToExpose;
    private $router;
    private $requestContextBaseUrl;
    private $cacheControl;
    private $_usedProperties = [];
    private $_hasDeprecatedCalls = false;

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function serializer($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['serializer'] = true;
        $this->serializer = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed>|mixed $value
     *
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function routesToExpose(mixed $value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['routesToExpose'] = true;
        $this->routesToExpose = $value;

        return $this;
    }

    /**
     * @default 'router'
     * @param ParamConfigurator|mixed $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function router($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['router'] = true;
        $this->router = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function requestContextBaseUrl($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['requestContextBaseUrl'] = true;
        $this->requestContextBaseUrl = $value;

        return $this;
    }

    /**
     * @deprecated since Symfony 7.4
     */
    public function cacheControl(array $value = []): \Symfony\Config\FosJsRouting\CacheControlConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (null === $this->cacheControl) {
            $this->_usedProperties['cacheControl'] = true;
            $this->cacheControl = new \Symfony\Config\FosJsRouting\CacheControlConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "cacheControl()" has already been initialized. You cannot pass values the second time you call cacheControl().');
        }

        return $this->cacheControl;
    }

    public function getExtensionAlias(): string
    {
        return 'fos_js_routing';
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('serializer', $config)) {
            $this->_usedProperties['serializer'] = true;
            $this->serializer = $config['serializer'];
            unset($config['serializer']);
        }

        if (array_key_exists('routes_to_expose', $config)) {
            $this->_usedProperties['routesToExpose'] = true;
            $this->routesToExpose = $config['routes_to_expose'];
            unset($config['routes_to_expose']);
        }

        if (array_key_exists('router', $config)) {
            $this->_usedProperties['router'] = true;
            $this->router = $config['router'];
            unset($config['router']);
        }

        if (array_key_exists('request_context_base_url', $config)) {
            $this->_usedProperties['requestContextBaseUrl'] = true;
            $this->requestContextBaseUrl = $config['request_context_base_url'];
            unset($config['request_context_base_url']);
        }

        if (array_key_exists('cache_control', $config)) {
            $this->_usedProperties['cacheControl'] = true;
            $this->cacheControl = new \Symfony\Config\FosJsRouting\CacheControlConfig($config['cache_control']);
            unset($config['cache_control']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['serializer'])) {
            $output['serializer'] = $this->serializer;
        }
        if (isset($this->_usedProperties['routesToExpose'])) {
            $output['routes_to_expose'] = $this->routesToExpose;
        }
        if (isset($this->_usedProperties['router'])) {
            $output['router'] = $this->router;
        }
        if (isset($this->_usedProperties['requestContextBaseUrl'])) {
            $output['request_context_base_url'] = $this->requestContextBaseUrl;
        }
        if (isset($this->_usedProperties['cacheControl'])) {
            $output['cache_control'] = $this->cacheControl->toArray();
        }
        if ($this->_hasDeprecatedCalls) {
            trigger_deprecation('symfony/config', '7.4', 'Calling any fluent method on "%s" is deprecated; pass the configuration to the constructor instead.', $this::class);
        }

        return $output;
    }

}
