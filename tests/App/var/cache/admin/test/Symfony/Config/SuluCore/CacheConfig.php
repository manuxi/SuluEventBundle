<?php

namespace Symfony\Config\SuluCore;

require_once __DIR__.\DIRECTORY_SEPARATOR.'Cache'.\DIRECTORY_SEPARATOR.'MemoizeConfig.php';

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class CacheConfig 
{
    private $memoize;
    private $_usedProperties = [];

    /**
     * @default {"default_lifetime":1}
     */
    public function memoize(array $value = []): \Symfony\Config\SuluCore\Cache\MemoizeConfig
    {
        if (null === $this->memoize) {
            $this->_usedProperties['memoize'] = true;
            $this->memoize = new \Symfony\Config\SuluCore\Cache\MemoizeConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "memoize()" has already been initialized. You cannot pass values the second time you call memoize().');
        }

        return $this->memoize;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('memoize', $config)) {
            $this->_usedProperties['memoize'] = true;
            $this->memoize = new \Symfony\Config\SuluCore\Cache\MemoizeConfig($config['memoize']);
            unset($config['memoize']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['memoize'])) {
            $output['memoize'] = $this->memoize->toArray();
        }

        return $output;
    }

}
