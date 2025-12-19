<?php

namespace Symfony\Config\SuluAdmin;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class FormsConfig 
{
    private $directories;
    private $_usedProperties = [];

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed> $value
     *
     * @return $this
     */
    public function directories(ParamConfigurator|array $value): static
    {
        $this->_usedProperties['directories'] = true;
        $this->directories = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('directories', $config)) {
            $this->_usedProperties['directories'] = true;
            $this->directories = $config['directories'];
            unset($config['directories']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['directories'])) {
            $output['directories'] = $this->directories;
        }

        return $output;
    }

}
