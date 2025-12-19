<?php

namespace Symfony\Config\FosRest\View;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class JsonpHandlerConfig 
{
    private $callbackParam;
    private $mimeType;
    private $_usedProperties = [];

    /**
     * @default 'callback'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function callbackParam($value): static
    {
        $this->_usedProperties['callbackParam'] = true;
        $this->callbackParam = $value;

        return $this;
    }

    /**
     * @default 'application/javascript+jsonp'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function mimeType($value): static
    {
        $this->_usedProperties['mimeType'] = true;
        $this->mimeType = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('callback_param', $config)) {
            $this->_usedProperties['callbackParam'] = true;
            $this->callbackParam = $config['callback_param'];
            unset($config['callback_param']);
        }

        if (array_key_exists('mime_type', $config)) {
            $this->_usedProperties['mimeType'] = true;
            $this->mimeType = $config['mime_type'];
            unset($config['mime_type']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['callbackParam'])) {
            $output['callback_param'] = $this->callbackParam;
        }
        if (isset($this->_usedProperties['mimeType'])) {
            $output['mime_type'] = $this->mimeType;
        }

        return $output;
    }

}
