<?php

namespace Symfony\Config\SuluMedia;

require_once __DIR__.\DIRECTORY_SEPARATOR.'Objects'.\DIRECTORY_SEPARATOR.'MediaConfig.php';

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class ObjectsConfig 
{
    private $media;
    private $_usedProperties = [];

    /**
     * @default {"model":"Sulu\\Bundle\\MediaBundle\\Entity\\Media","repository":"Sulu\\Bundle\\MediaBundle\\Entity\\MediaRepository"}
     */
    public function media(array $value = []): \Symfony\Config\SuluMedia\Objects\MediaConfig
    {
        if (null === $this->media) {
            $this->_usedProperties['media'] = true;
            $this->media = new \Symfony\Config\SuluMedia\Objects\MediaConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "media()" has already been initialized. You cannot pass values the second time you call media().');
        }

        return $this->media;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('media', $config)) {
            $this->_usedProperties['media'] = true;
            $this->media = new \Symfony\Config\SuluMedia\Objects\MediaConfig($config['media']);
            unset($config['media']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['media'])) {
            $output['media'] = $this->media->toArray();
        }

        return $output;
    }

}
