<?php

namespace Symfony\Config\SuluTrash;

require_once __DIR__.\DIRECTORY_SEPARATOR.'Objects'.\DIRECTORY_SEPARATOR.'TrashItemConfig.php';

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class ObjectsConfig 
{
    private $trashItem;
    private $_usedProperties = [];

    /**
     * @default {"model":"Sulu\\Bundle\\TrashBundle\\Domain\\Model\\TrashItem"}
     */
    public function trashItem(array $value = []): \Symfony\Config\SuluTrash\Objects\TrashItemConfig
    {
        if (null === $this->trashItem) {
            $this->_usedProperties['trashItem'] = true;
            $this->trashItem = new \Symfony\Config\SuluTrash\Objects\TrashItemConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "trashItem()" has already been initialized. You cannot pass values the second time you call trashItem().');
        }

        return $this->trashItem;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('trash_item', $config)) {
            $this->_usedProperties['trashItem'] = true;
            $this->trashItem = new \Symfony\Config\SuluTrash\Objects\TrashItemConfig($config['trash_item']);
            unset($config['trash_item']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['trashItem'])) {
            $output['trash_item'] = $this->trashItem->toArray();
        }

        return $output;
    }

}
