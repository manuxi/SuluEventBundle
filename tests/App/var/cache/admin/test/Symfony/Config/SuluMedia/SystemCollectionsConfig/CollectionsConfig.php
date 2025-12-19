<?php

namespace Symfony\Config\SuluMedia\SystemCollectionsConfig;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class CollectionsConfig 
{
    private $metaTitle;
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

    public function __construct(array $config = [])
    {
        if (array_key_exists('meta_title', $config)) {
            $this->_usedProperties['metaTitle'] = true;
            $this->metaTitle = $config['meta_title'];
            unset($config['meta_title']);
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

        return $output;
    }

}
