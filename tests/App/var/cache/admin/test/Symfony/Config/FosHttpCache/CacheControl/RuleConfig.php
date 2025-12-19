<?php

namespace Symfony\Config\FosHttpCache\CacheControl;

require_once __DIR__.\DIRECTORY_SEPARATOR.'RuleConfig'.\DIRECTORY_SEPARATOR.'MatchConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'RuleConfig'.\DIRECTORY_SEPARATOR.'HeadersConfig.php';

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class RuleConfig 
{
    private $match;
    private $headers;
    private $_usedProperties = [];

    public function match(array $value = []): \Symfony\Config\FosHttpCache\CacheControl\RuleConfig\MatchConfig
    {
        if (null === $this->match) {
            $this->_usedProperties['match'] = true;
            $this->match = new \Symfony\Config\FosHttpCache\CacheControl\RuleConfig\MatchConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "match()" has already been initialized. You cannot pass values the second time you call match().');
        }

        return $this->match;
    }

    public function headers(array $value = []): \Symfony\Config\FosHttpCache\CacheControl\RuleConfig\HeadersConfig
    {
        if (null === $this->headers) {
            $this->_usedProperties['headers'] = true;
            $this->headers = new \Symfony\Config\FosHttpCache\CacheControl\RuleConfig\HeadersConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "headers()" has already been initialized. You cannot pass values the second time you call headers().');
        }

        return $this->headers;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('match', $config)) {
            $this->_usedProperties['match'] = true;
            $this->match = new \Symfony\Config\FosHttpCache\CacheControl\RuleConfig\MatchConfig($config['match']);
            unset($config['match']);
        }

        if (array_key_exists('headers', $config)) {
            $this->_usedProperties['headers'] = true;
            $this->headers = new \Symfony\Config\FosHttpCache\CacheControl\RuleConfig\HeadersConfig($config['headers']);
            unset($config['headers']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['match'])) {
            $output['match'] = $this->match->toArray();
        }
        if (isset($this->_usedProperties['headers'])) {
            $output['headers'] = $this->headers->toArray();
        }

        return $output;
    }

}
