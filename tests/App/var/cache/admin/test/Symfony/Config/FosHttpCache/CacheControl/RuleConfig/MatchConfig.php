<?php

namespace Symfony\Config\FosHttpCache\CacheControl\RuleConfig;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class MatchConfig 
{
    private $path;
    private $queryString;
    private $host;
    private $methods;
    private $ips;
    private $attributes;
    private $additionalResponseStatus;
    private $matchResponse;
    private $expressionLanguage;
    private $_usedProperties = [];

    /**
     * Request path.
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function path($value): static
    {
        $this->_usedProperties['path'] = true;
        $this->path = $value;

        return $this;
    }

    /**
     * Request query string.
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function queryString($value): static
    {
        $this->_usedProperties['queryString'] = true;
        $this->queryString = $value;

        return $this;
    }

    /**
     * Request host name.
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function host($value): static
    {
        $this->_usedProperties['host'] = true;
        $this->host = $value;

        return $this;
    }

    /**
     * @return $this
     */
    public function method(string $name, mixed $value): static
    {
        $this->_usedProperties['methods'] = true;
        $this->methods[$name] = $value;

        return $this;
    }

    /**
     * @return $this
     */
    public function ip(string $name, mixed $value): static
    {
        $this->_usedProperties['ips'] = true;
        $this->ips[$name] = $value;

        return $this;
    }

    /**
     * @return $this
     */
    public function attribute(string $name, mixed $value): static
    {
        $this->_usedProperties['attributes'] = true;
        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed> $value
     *
     * @return $this
     */
    public function additionalResponseStatus(ParamConfigurator|array $value): static
    {
        $this->_usedProperties['additionalResponseStatus'] = true;
        $this->additionalResponseStatus = $value;

        return $this;
    }

    /**
     * Expression to decide whether response should be matched. Replaces cacheable configuration.
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function matchResponse($value): static
    {
        $this->_usedProperties['matchResponse'] = true;
        $this->matchResponse = $value;

        return $this;
    }

    /**
     * Service name of a custom ExpressionLanguage to use.
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function expressionLanguage($value): static
    {
        $this->_usedProperties['expressionLanguage'] = true;
        $this->expressionLanguage = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('path', $config)) {
            $this->_usedProperties['path'] = true;
            $this->path = $config['path'];
            unset($config['path']);
        }

        if (array_key_exists('query_string', $config)) {
            $this->_usedProperties['queryString'] = true;
            $this->queryString = $config['query_string'];
            unset($config['query_string']);
        }

        if (array_key_exists('host', $config)) {
            $this->_usedProperties['host'] = true;
            $this->host = $config['host'];
            unset($config['host']);
        }

        if (array_key_exists('methods', $config)) {
            $this->_usedProperties['methods'] = true;
            $this->methods = $config['methods'];
            unset($config['methods']);
        }

        if (array_key_exists('ips', $config)) {
            $this->_usedProperties['ips'] = true;
            $this->ips = $config['ips'];
            unset($config['ips']);
        }

        if (array_key_exists('attributes', $config)) {
            $this->_usedProperties['attributes'] = true;
            $this->attributes = $config['attributes'];
            unset($config['attributes']);
        }

        if (array_key_exists('additional_response_status', $config)) {
            $this->_usedProperties['additionalResponseStatus'] = true;
            $this->additionalResponseStatus = $config['additional_response_status'];
            unset($config['additional_response_status']);
        }

        if (array_key_exists('match_response', $config)) {
            $this->_usedProperties['matchResponse'] = true;
            $this->matchResponse = $config['match_response'];
            unset($config['match_response']);
        }

        if (array_key_exists('expression_language', $config)) {
            $this->_usedProperties['expressionLanguage'] = true;
            $this->expressionLanguage = $config['expression_language'];
            unset($config['expression_language']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['path'])) {
            $output['path'] = $this->path;
        }
        if (isset($this->_usedProperties['queryString'])) {
            $output['query_string'] = $this->queryString;
        }
        if (isset($this->_usedProperties['host'])) {
            $output['host'] = $this->host;
        }
        if (isset($this->_usedProperties['methods'])) {
            $output['methods'] = $this->methods;
        }
        if (isset($this->_usedProperties['ips'])) {
            $output['ips'] = $this->ips;
        }
        if (isset($this->_usedProperties['attributes'])) {
            $output['attributes'] = $this->attributes;
        }
        if (isset($this->_usedProperties['additionalResponseStatus'])) {
            $output['additional_response_status'] = $this->additionalResponseStatus;
        }
        if (isset($this->_usedProperties['matchResponse'])) {
            $output['match_response'] = $this->matchResponse;
        }
        if (isset($this->_usedProperties['expressionLanguage'])) {
            $output['expression_language'] = $this->expressionLanguage;
        }

        return $output;
    }

}
