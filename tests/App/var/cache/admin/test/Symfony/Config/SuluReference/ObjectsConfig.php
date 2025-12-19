<?php

namespace Symfony\Config\SuluReference;

require_once __DIR__.\DIRECTORY_SEPARATOR.'Objects'.\DIRECTORY_SEPARATOR.'ReferenceConfig.php';

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class ObjectsConfig 
{
    private $reference;
    private $_usedProperties = [];

    /**
     * @default {"model":"Sulu\\Bundle\\ReferenceBundle\\Domain\\Model\\Reference"}
     */
    public function reference(array $value = []): \Symfony\Config\SuluReference\Objects\ReferenceConfig
    {
        if (null === $this->reference) {
            $this->_usedProperties['reference'] = true;
            $this->reference = new \Symfony\Config\SuluReference\Objects\ReferenceConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "reference()" has already been initialized. You cannot pass values the second time you call reference().');
        }

        return $this->reference;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('reference', $config)) {
            $this->_usedProperties['reference'] = true;
            $this->reference = new \Symfony\Config\SuluReference\Objects\ReferenceConfig($config['reference']);
            unset($config['reference']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['reference'])) {
            $output['reference'] = $this->reference->toArray();
        }

        return $output;
    }

}
