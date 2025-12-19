<?php

namespace Symfony\Config\FosHttpCache\Invalidation\RuleConfig;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class RouteConfig 
{
    private $ignoreExtraParams;
    private $_usedProperties = [];

    /**
     * @default true
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function ignoreExtraParams($value): static
    {
        $this->_usedProperties['ignoreExtraParams'] = true;
        $this->ignoreExtraParams = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('ignore_extra_params', $config)) {
            $this->_usedProperties['ignoreExtraParams'] = true;
            $this->ignoreExtraParams = $config['ignore_extra_params'];
            unset($config['ignore_extra_params']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['ignoreExtraParams'])) {
            $output['ignore_extra_params'] = $this->ignoreExtraParams;
        }

        return $output;
    }

}
