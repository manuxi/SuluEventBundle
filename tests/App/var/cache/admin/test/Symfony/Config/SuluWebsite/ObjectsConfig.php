<?php

namespace Symfony\Config\SuluWebsite;

require_once __DIR__.\DIRECTORY_SEPARATOR.'Objects'.\DIRECTORY_SEPARATOR.'AnalyticsConfig.php';

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class ObjectsConfig 
{
    private $analytics;
    private $_usedProperties = [];

    /**
     * @default {"model":"Sulu\\Bundle\\WebsiteBundle\\Entity\\Analytics","repository":"Sulu\\Bundle\\WebsiteBundle\\Entity\\AnalyticsRepository"}
     */
    public function analytics(array $value = []): \Symfony\Config\SuluWebsite\Objects\AnalyticsConfig
    {
        if (null === $this->analytics) {
            $this->_usedProperties['analytics'] = true;
            $this->analytics = new \Symfony\Config\SuluWebsite\Objects\AnalyticsConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "analytics()" has already been initialized. You cannot pass values the second time you call analytics().');
        }

        return $this->analytics;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('analytics', $config)) {
            $this->_usedProperties['analytics'] = true;
            $this->analytics = new \Symfony\Config\SuluWebsite\Objects\AnalyticsConfig($config['analytics']);
            unset($config['analytics']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['analytics'])) {
            $output['analytics'] = $this->analytics->toArray();
        }

        return $output;
    }

}
