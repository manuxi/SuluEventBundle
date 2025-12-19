<?php

namespace Symfony\Config;

require_once __DIR__.\DIRECTORY_SEPARATOR.'SuluLocation'.\DIRECTORY_SEPARATOR.'GeolocatorsConfig.php';

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class SuluLocationConfig implements \Symfony\Component\Config\Builder\ConfigBuilderInterface
{
    private $geolocator;
    private $geolocators;
    private $_usedProperties = [];
    private $_hasDeprecatedCalls = false;

    /**
     * @default 'nominatim'
     * @param ParamConfigurator|'nominatim'|'google'|'mapquest' $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function geolocator($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['geolocator'] = true;
        $this->geolocator = $value;

        return $this;
    }

    /**
     * @default {"nominatim":{"api_key":"","endpoint":"https:\/\/nominatim.openstreetmap.org\/search"},"google":{"api_key":""},"mapquest":{"api_key":"","endpoint":"https:\/\/www.mapquestapi.com\/geocoding\/v1\/address"}}
     * @deprecated since Symfony 7.4
     */
    public function geolocators(array $value = []): \Symfony\Config\SuluLocation\GeolocatorsConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (null === $this->geolocators) {
            $this->_usedProperties['geolocators'] = true;
            $this->geolocators = new \Symfony\Config\SuluLocation\GeolocatorsConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "geolocators()" has already been initialized. You cannot pass values the second time you call geolocators().');
        }

        return $this->geolocators;
    }

    public function getExtensionAlias(): string
    {
        return 'sulu_location';
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('geolocator', $config)) {
            $this->_usedProperties['geolocator'] = true;
            $this->geolocator = $config['geolocator'];
            unset($config['geolocator']);
        }

        if (array_key_exists('geolocators', $config)) {
            $this->_usedProperties['geolocators'] = true;
            $this->geolocators = new \Symfony\Config\SuluLocation\GeolocatorsConfig($config['geolocators']);
            unset($config['geolocators']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['geolocator'])) {
            $output['geolocator'] = $this->geolocator;
        }
        if (isset($this->_usedProperties['geolocators'])) {
            $output['geolocators'] = $this->geolocators->toArray();
        }
        if ($this->_hasDeprecatedCalls) {
            trigger_deprecation('symfony/config', '7.4', 'Calling any fluent method on "%s" is deprecated; pass the configuration to the constructor instead.', $this::class);
        }

        return $output;
    }

}
