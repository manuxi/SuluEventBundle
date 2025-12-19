<?php

namespace Symfony\Config\SuluTag;

require_once __DIR__.\DIRECTORY_SEPARATOR.'Objects'.\DIRECTORY_SEPARATOR.'TagConfig.php';

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class ObjectsConfig 
{
    private $tag;
    private $_usedProperties = [];

    /**
     * @default {"model":"Sulu\\Bundle\\TagBundle\\Entity\\Tag","repository":"Sulu\\Bundle\\TagBundle\\Entity\\TagRepository"}
     */
    public function tag(array $value = []): \Symfony\Config\SuluTag\Objects\TagConfig
    {
        if (null === $this->tag) {
            $this->_usedProperties['tag'] = true;
            $this->tag = new \Symfony\Config\SuluTag\Objects\TagConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "tag()" has already been initialized. You cannot pass values the second time you call tag().');
        }

        return $this->tag;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('tag', $config)) {
            $this->_usedProperties['tag'] = true;
            $this->tag = new \Symfony\Config\SuluTag\Objects\TagConfig($config['tag']);
            unset($config['tag']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['tag'])) {
            $output['tag'] = $this->tag->toArray();
        }

        return $output;
    }

}
