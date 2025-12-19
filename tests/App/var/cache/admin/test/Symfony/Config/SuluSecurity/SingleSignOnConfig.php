<?php

namespace Symfony\Config\SuluSecurity;

require_once __DIR__.\DIRECTORY_SEPARATOR.'SingleSignOn'.\DIRECTORY_SEPARATOR.'ProvidersConfig.php';

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class SingleSignOnConfig 
{
    private $providers;
    private $_usedProperties = [];

    public function providers(string $domain, array $value = []): \Symfony\Config\SuluSecurity\SingleSignOn\ProvidersConfig
    {
        if (!isset($this->providers[$domain])) {
            $this->_usedProperties['providers'] = true;
            $this->providers[$domain] = new \Symfony\Config\SuluSecurity\SingleSignOn\ProvidersConfig($value);
        } elseif (1 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "providers()" has already been initialized. You cannot pass values the second time you call providers().');
        }

        return $this->providers[$domain];
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('providers', $config)) {
            $this->_usedProperties['providers'] = true;
            $this->providers = array_map(fn ($v) => new \Symfony\Config\SuluSecurity\SingleSignOn\ProvidersConfig($v), $config['providers']);
            unset($config['providers']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['providers'])) {
            $output['providers'] = array_map(fn ($v) => $v->toArray(), $this->providers);
        }

        return $output;
    }

}
