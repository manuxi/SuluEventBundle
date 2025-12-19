<?php

namespace Symfony\Config\SuluMedia;

require_once __DIR__.\DIRECTORY_SEPARATOR.'SystemCollectionsConfig'.\DIRECTORY_SEPARATOR.'CollectionsConfig.php';

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class SystemCollectionsConfig 
{
    private $metaTitle;
    private $collections;
    private $_usedProperties = [];

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed> $value
     *
     * @return $this
     */
    public function metaTitle(ParamConfigurator|array $value): static
    {
        $this->_usedProperties['metaTitle'] = true;
        $this->metaTitle = $value;

        return $this;
    }

    public function collections(string $key, array $value = []): \Symfony\Config\SuluMedia\SystemCollectionsConfig\CollectionsConfig
    {
        if (!isset($this->collections[$key])) {
            $this->_usedProperties['collections'] = true;
            $this->collections[$key] = new \Symfony\Config\SuluMedia\SystemCollectionsConfig\CollectionsConfig($value);
        } elseif (1 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "collections()" has already been initialized. You cannot pass values the second time you call collections().');
        }

        return $this->collections[$key];
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('meta_title', $config)) {
            $this->_usedProperties['metaTitle'] = true;
            $this->metaTitle = $config['meta_title'];
            unset($config['meta_title']);
        }

        if (array_key_exists('collections', $config)) {
            $this->_usedProperties['collections'] = true;
            $this->collections = array_map(fn ($v) => new \Symfony\Config\SuluMedia\SystemCollectionsConfig\CollectionsConfig($v), $config['collections']);
            unset($config['collections']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['metaTitle'])) {
            $output['meta_title'] = $this->metaTitle;
        }
        if (isset($this->_usedProperties['collections'])) {
            $output['collections'] = array_map(fn ($v) => $v->toArray(), $this->collections);
        }

        return $output;
    }

}
