<?php

namespace Symfony\Config\FosHttpCache\CacheControl;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class DefaultsConfig 
{
    private $overwrite;
    private $_usedProperties = [];

    /**
     * Whether to overwrite existing cache headers
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function overwrite($value): static
    {
        $this->_usedProperties['overwrite'] = true;
        $this->overwrite = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('overwrite', $config)) {
            $this->_usedProperties['overwrite'] = true;
            $this->overwrite = $config['overwrite'];
            unset($config['overwrite']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['overwrite'])) {
            $output['overwrite'] = $this->overwrite;
        }

        return $output;
    }

}
