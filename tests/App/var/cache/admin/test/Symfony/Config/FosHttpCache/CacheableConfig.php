<?php

namespace Symfony\Config\FosHttpCache;

require_once __DIR__.\DIRECTORY_SEPARATOR.'Cacheable'.\DIRECTORY_SEPARATOR.'ResponseConfig.php';

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class CacheableConfig 
{
    private $response;
    private $_usedProperties = [];

    /**
     * @default {"additional_status":[],"expression":null}
     */
    public function response(array $value = []): \Symfony\Config\FosHttpCache\Cacheable\ResponseConfig
    {
        if (null === $this->response) {
            $this->_usedProperties['response'] = true;
            $this->response = new \Symfony\Config\FosHttpCache\Cacheable\ResponseConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "response()" has already been initialized. You cannot pass values the second time you call response().');
        }

        return $this->response;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('response', $config)) {
            $this->_usedProperties['response'] = true;
            $this->response = new \Symfony\Config\FosHttpCache\Cacheable\ResponseConfig($config['response']);
            unset($config['response']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['response'])) {
            $output['response'] = $this->response->toArray();
        }

        return $output;
    }

}
