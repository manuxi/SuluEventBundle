<?php

namespace Symfony\Config\FosHttpCache\Tags;

require_once __DIR__.\DIRECTORY_SEPARATOR.'RuleConfig'.\DIRECTORY_SEPARATOR.'MatchConfig.php';

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Loader\ParamConfigurator;

/**
 * This class is automatically generated to help in creating a config.
 */
class RuleConfig 
{
    private $match;
    private $tags;
    private $tagExpressions;
    private $_usedProperties = [];

    public function match(array $value = []): \Symfony\Config\FosHttpCache\Tags\RuleConfig\MatchConfig
    {
        if (null === $this->match) {
            $this->_usedProperties['match'] = true;
            $this->match = new \Symfony\Config\FosHttpCache\Tags\RuleConfig\MatchConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "match()" has already been initialized. You cannot pass values the second time you call match().');
        }

        return $this->match;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed> $value
     *
     * @return $this
     */
    public function tags(ParamConfigurator|array $value): static
    {
        $this->_usedProperties['tags'] = true;
        $this->tags = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed> $value
     *
     * @return $this
     */
    public function tagExpressions(ParamConfigurator|array $value): static
    {
        $this->_usedProperties['tagExpressions'] = true;
        $this->tagExpressions = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('match', $config)) {
            $this->_usedProperties['match'] = true;
            $this->match = new \Symfony\Config\FosHttpCache\Tags\RuleConfig\MatchConfig($config['match']);
            unset($config['match']);
        }

        if (array_key_exists('tags', $config)) {
            $this->_usedProperties['tags'] = true;
            $this->tags = $config['tags'];
            unset($config['tags']);
        }

        if (array_key_exists('tag_expressions', $config)) {
            $this->_usedProperties['tagExpressions'] = true;
            $this->tagExpressions = $config['tag_expressions'];
            unset($config['tag_expressions']);
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
        if (isset($this->_usedProperties['tags'])) {
            $output['tags'] = $this->tags;
        }
        if (isset($this->_usedProperties['tagExpressions'])) {
            $output['tag_expressions'] = $this->tagExpressions;
        }

        return $output;
    }

}
