<?php

namespace Symfony\Config\SuluPreview;

require_once __DIR__.\DIRECTORY_SEPARATOR.'Objects'.\DIRECTORY_SEPARATOR.'PreviewLinkConfig.php';

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class ObjectsConfig 
{
    private $previewLink;
    private $_usedProperties = [];

    /**
     * @default {"model":"Sulu\\Bundle\\PreviewBundle\\Domain\\Model\\PreviewLink"}
     */
    public function previewLink(array $value = []): \Symfony\Config\SuluPreview\Objects\PreviewLinkConfig
    {
        if (null === $this->previewLink) {
            $this->_usedProperties['previewLink'] = true;
            $this->previewLink = new \Symfony\Config\SuluPreview\Objects\PreviewLinkConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "previewLink()" has already been initialized. You cannot pass values the second time you call previewLink().');
        }

        return $this->previewLink;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('preview_link', $config)) {
            $this->_usedProperties['previewLink'] = true;
            $this->previewLink = new \Symfony\Config\SuluPreview\Objects\PreviewLinkConfig($config['preview_link']);
            unset($config['preview_link']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['previewLink'])) {
            $output['preview_link'] = $this->previewLink->toArray();
        }

        return $output;
    }

}
