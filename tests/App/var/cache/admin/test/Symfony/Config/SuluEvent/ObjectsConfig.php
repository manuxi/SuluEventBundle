<?php

namespace Symfony\Config\SuluEvent;

require_once __DIR__.\DIRECTORY_SEPARATOR.'Objects'.\DIRECTORY_SEPARATOR.'EventConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Objects'.\DIRECTORY_SEPARATOR.'EventDimensionContentConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Objects'.\DIRECTORY_SEPARATOR.'LocationConfig.php';

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class ObjectsConfig 
{
    private $event;
    private $eventDimensionContent;
    private $location;
    private $_usedProperties = [];

    /**
     * @default {"model":"Manuxi\\SuluEventBundle\\Entity\\Event","repository":"Manuxi\\SuluEventBundle\\Repository\\EventRepository"}
     */
    public function event(array $value = []): \Symfony\Config\SuluEvent\Objects\EventConfig
    {
        if (null === $this->event) {
            $this->_usedProperties['event'] = true;
            $this->event = new \Symfony\Config\SuluEvent\Objects\EventConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "event()" has already been initialized. You cannot pass values the second time you call event().');
        }

        return $this->event;
    }

    /**
     * @default {"model":"Manuxi\\SuluEventBundle\\Entity\\EventDimensionContent","repository":"Manuxi\\SuluEventBundle\\Repository\\EventDimensionContentRepository"}
     */
    public function eventDimensionContent(array $value = []): \Symfony\Config\SuluEvent\Objects\EventDimensionContentConfig
    {
        if (null === $this->eventDimensionContent) {
            $this->_usedProperties['eventDimensionContent'] = true;
            $this->eventDimensionContent = new \Symfony\Config\SuluEvent\Objects\EventDimensionContentConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "eventDimensionContent()" has already been initialized. You cannot pass values the second time you call eventDimensionContent().');
        }

        return $this->eventDimensionContent;
    }

    /**
     * @default {"model":"Manuxi\\SuluEventBundle\\Entity\\Location","repository":"Manuxi\\SuluEventBundle\\Repository\\LocationRepository"}
     */
    public function location(array $value = []): \Symfony\Config\SuluEvent\Objects\LocationConfig
    {
        if (null === $this->location) {
            $this->_usedProperties['location'] = true;
            $this->location = new \Symfony\Config\SuluEvent\Objects\LocationConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "location()" has already been initialized. You cannot pass values the second time you call location().');
        }

        return $this->location;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('event', $config)) {
            $this->_usedProperties['event'] = true;
            $this->event = new \Symfony\Config\SuluEvent\Objects\EventConfig($config['event']);
            unset($config['event']);
        }

        if (array_key_exists('event_dimension_content', $config)) {
            $this->_usedProperties['eventDimensionContent'] = true;
            $this->eventDimensionContent = new \Symfony\Config\SuluEvent\Objects\EventDimensionContentConfig($config['event_dimension_content']);
            unset($config['event_dimension_content']);
        }

        if (array_key_exists('location', $config)) {
            $this->_usedProperties['location'] = true;
            $this->location = new \Symfony\Config\SuluEvent\Objects\LocationConfig($config['location']);
            unset($config['location']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['event'])) {
            $output['event'] = $this->event->toArray();
        }
        if (isset($this->_usedProperties['eventDimensionContent'])) {
            $output['event_dimension_content'] = $this->eventDimensionContent->toArray();
        }
        if (isset($this->_usedProperties['location'])) {
            $output['location'] = $this->location->toArray();
        }

        return $output;
    }

}
