<?php

namespace Symfony\Config\SuluAdmin\ResourcesConfig;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class RoutesConfig 
{
    private $list;
    private $detail;
    private $_usedProperties = [];

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function list($value): static
    {
        $this->_usedProperties['list'] = true;
        $this->list = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function detail($value): static
    {
        $this->_usedProperties['detail'] = true;
        $this->detail = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('list', $config)) {
            $this->_usedProperties['list'] = true;
            $this->list = $config['list'];
            unset($config['list']);
        }

        if (array_key_exists('detail', $config)) {
            $this->_usedProperties['detail'] = true;
            $this->detail = $config['detail'];
            unset($config['detail']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['list'])) {
            $output['list'] = $this->list;
        }
        if (isset($this->_usedProperties['detail'])) {
            $output['detail'] = $this->detail;
        }

        return $output;
    }

}
