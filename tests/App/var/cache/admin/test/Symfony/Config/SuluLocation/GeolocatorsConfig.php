<?php

namespace Symfony\Config\SuluLocation;

require_once __DIR__.\DIRECTORY_SEPARATOR.'Geolocators'.\DIRECTORY_SEPARATOR.'NominatimConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Geolocators'.\DIRECTORY_SEPARATOR.'GoogleConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Geolocators'.\DIRECTORY_SEPARATOR.'MapquestConfig.php';

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class GeolocatorsConfig 
{
    private $nominatim;
    private $google;
    private $mapquest;
    private $_usedProperties = [];

    /**
     * @default {"api_key":"","endpoint":"https:\/\/nominatim.openstreetmap.org\/search"}
     */
    public function nominatim(array $value = []): \Symfony\Config\SuluLocation\Geolocators\NominatimConfig
    {
        if (null === $this->nominatim) {
            $this->_usedProperties['nominatim'] = true;
            $this->nominatim = new \Symfony\Config\SuluLocation\Geolocators\NominatimConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "nominatim()" has already been initialized. You cannot pass values the second time you call nominatim().');
        }

        return $this->nominatim;
    }

    /**
     * @default {"api_key":""}
     */
    public function google(array $value = []): \Symfony\Config\SuluLocation\Geolocators\GoogleConfig
    {
        if (null === $this->google) {
            $this->_usedProperties['google'] = true;
            $this->google = new \Symfony\Config\SuluLocation\Geolocators\GoogleConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "google()" has already been initialized. You cannot pass values the second time you call google().');
        }

        return $this->google;
    }

    /**
     * @default {"api_key":"","endpoint":"https:\/\/www.mapquestapi.com\/geocoding\/v1\/address"}
     */
    public function mapquest(array $value = []): \Symfony\Config\SuluLocation\Geolocators\MapquestConfig
    {
        if (null === $this->mapquest) {
            $this->_usedProperties['mapquest'] = true;
            $this->mapquest = new \Symfony\Config\SuluLocation\Geolocators\MapquestConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "mapquest()" has already been initialized. You cannot pass values the second time you call mapquest().');
        }

        return $this->mapquest;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('nominatim', $config)) {
            $this->_usedProperties['nominatim'] = true;
            $this->nominatim = new \Symfony\Config\SuluLocation\Geolocators\NominatimConfig($config['nominatim']);
            unset($config['nominatim']);
        }

        if (array_key_exists('google', $config)) {
            $this->_usedProperties['google'] = true;
            $this->google = new \Symfony\Config\SuluLocation\Geolocators\GoogleConfig($config['google']);
            unset($config['google']);
        }

        if (array_key_exists('mapquest', $config)) {
            $this->_usedProperties['mapquest'] = true;
            $this->mapquest = new \Symfony\Config\SuluLocation\Geolocators\MapquestConfig($config['mapquest']);
            unset($config['mapquest']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['nominatim'])) {
            $output['nominatim'] = $this->nominatim->toArray();
        }
        if (isset($this->_usedProperties['google'])) {
            $output['google'] = $this->google->toArray();
        }
        if (isset($this->_usedProperties['mapquest'])) {
            $output['mapquest'] = $this->mapquest->toArray();
        }

        return $output;
    }

}
