<?php

namespace Symfony\Config\SuluPage;

require_once __DIR__.\DIRECTORY_SEPARATOR.'Objects'.\DIRECTORY_SEPARATOR.'PageConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Objects'.\DIRECTORY_SEPARATOR.'PageContentConfig.php';

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class ObjectsConfig 
{
    private $page;
    private $pageContent;
    private $_usedProperties = [];

    /**
     * @default {"model":"Sulu\\Page\\Domain\\Model\\Page"}
     */
    public function page(array $value = []): \Symfony\Config\SuluPage\Objects\PageConfig
    {
        if (null === $this->page) {
            $this->_usedProperties['page'] = true;
            $this->page = new \Symfony\Config\SuluPage\Objects\PageConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "page()" has already been initialized. You cannot pass values the second time you call page().');
        }

        return $this->page;
    }

    /**
     * @default {"model":"Sulu\\Page\\Domain\\Model\\PageDimensionContent"}
     */
    public function pageContent(array $value = []): \Symfony\Config\SuluPage\Objects\PageContentConfig
    {
        if (null === $this->pageContent) {
            $this->_usedProperties['pageContent'] = true;
            $this->pageContent = new \Symfony\Config\SuluPage\Objects\PageContentConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "pageContent()" has already been initialized. You cannot pass values the second time you call pageContent().');
        }

        return $this->pageContent;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('page', $config)) {
            $this->_usedProperties['page'] = true;
            $this->page = new \Symfony\Config\SuluPage\Objects\PageConfig($config['page']);
            unset($config['page']);
        }

        if (array_key_exists('page_content', $config)) {
            $this->_usedProperties['pageContent'] = true;
            $this->pageContent = new \Symfony\Config\SuluPage\Objects\PageContentConfig($config['page_content']);
            unset($config['page_content']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['page'])) {
            $output['page'] = $this->page->toArray();
        }
        if (isset($this->_usedProperties['pageContent'])) {
            $output['page_content'] = $this->pageContent->toArray();
        }

        return $output;
    }

}
