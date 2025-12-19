<?php

namespace Symfony\Config\StofDoctrineExtensions;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class ClassConfig 
{
    private $translatable;
    private $timestampable;
    private $blameable;
    private $sluggable;
    private $tree;
    private $loggable;
    private $sortable;
    private $softdeleteable;
    private $uploadable;
    private $referenceIntegrity;
    private $_usedProperties = [];

    /**
     * @default 'Gedmo\\Translatable\\TranslatableListener'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function translatable($value): static
    {
        $this->_usedProperties['translatable'] = true;
        $this->translatable = $value;

        return $this;
    }

    /**
     * @default 'Gedmo\\Timestampable\\TimestampableListener'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function timestampable($value): static
    {
        $this->_usedProperties['timestampable'] = true;
        $this->timestampable = $value;

        return $this;
    }

    /**
     * @default 'Gedmo\\Blameable\\BlameableListener'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function blameable($value): static
    {
        $this->_usedProperties['blameable'] = true;
        $this->blameable = $value;

        return $this;
    }

    /**
     * @default 'Gedmo\\Sluggable\\SluggableListener'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function sluggable($value): static
    {
        $this->_usedProperties['sluggable'] = true;
        $this->sluggable = $value;

        return $this;
    }

    /**
     * @default 'Gedmo\\Tree\\TreeListener'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function tree($value): static
    {
        $this->_usedProperties['tree'] = true;
        $this->tree = $value;

        return $this;
    }

    /**
     * @default 'Gedmo\\Loggable\\LoggableListener'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function loggable($value): static
    {
        $this->_usedProperties['loggable'] = true;
        $this->loggable = $value;

        return $this;
    }

    /**
     * @default 'Gedmo\\Sortable\\SortableListener'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function sortable($value): static
    {
        $this->_usedProperties['sortable'] = true;
        $this->sortable = $value;

        return $this;
    }

    /**
     * @default 'Gedmo\\SoftDeleteable\\SoftDeleteableListener'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function softdeleteable($value): static
    {
        $this->_usedProperties['softdeleteable'] = true;
        $this->softdeleteable = $value;

        return $this;
    }

    /**
     * @default 'Gedmo\\Uploadable\\UploadableListener'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function uploadable($value): static
    {
        $this->_usedProperties['uploadable'] = true;
        $this->uploadable = $value;

        return $this;
    }

    /**
     * @default 'Gedmo\\ReferenceIntegrity\\ReferenceIntegrityListener'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function referenceIntegrity($value): static
    {
        $this->_usedProperties['referenceIntegrity'] = true;
        $this->referenceIntegrity = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('translatable', $config)) {
            $this->_usedProperties['translatable'] = true;
            $this->translatable = $config['translatable'];
            unset($config['translatable']);
        }

        if (array_key_exists('timestampable', $config)) {
            $this->_usedProperties['timestampable'] = true;
            $this->timestampable = $config['timestampable'];
            unset($config['timestampable']);
        }

        if (array_key_exists('blameable', $config)) {
            $this->_usedProperties['blameable'] = true;
            $this->blameable = $config['blameable'];
            unset($config['blameable']);
        }

        if (array_key_exists('sluggable', $config)) {
            $this->_usedProperties['sluggable'] = true;
            $this->sluggable = $config['sluggable'];
            unset($config['sluggable']);
        }

        if (array_key_exists('tree', $config)) {
            $this->_usedProperties['tree'] = true;
            $this->tree = $config['tree'];
            unset($config['tree']);
        }

        if (array_key_exists('loggable', $config)) {
            $this->_usedProperties['loggable'] = true;
            $this->loggable = $config['loggable'];
            unset($config['loggable']);
        }

        if (array_key_exists('sortable', $config)) {
            $this->_usedProperties['sortable'] = true;
            $this->sortable = $config['sortable'];
            unset($config['sortable']);
        }

        if (array_key_exists('softdeleteable', $config)) {
            $this->_usedProperties['softdeleteable'] = true;
            $this->softdeleteable = $config['softdeleteable'];
            unset($config['softdeleteable']);
        }

        if (array_key_exists('uploadable', $config)) {
            $this->_usedProperties['uploadable'] = true;
            $this->uploadable = $config['uploadable'];
            unset($config['uploadable']);
        }

        if (array_key_exists('reference_integrity', $config)) {
            $this->_usedProperties['referenceIntegrity'] = true;
            $this->referenceIntegrity = $config['reference_integrity'];
            unset($config['reference_integrity']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['translatable'])) {
            $output['translatable'] = $this->translatable;
        }
        if (isset($this->_usedProperties['timestampable'])) {
            $output['timestampable'] = $this->timestampable;
        }
        if (isset($this->_usedProperties['blameable'])) {
            $output['blameable'] = $this->blameable;
        }
        if (isset($this->_usedProperties['sluggable'])) {
            $output['sluggable'] = $this->sluggable;
        }
        if (isset($this->_usedProperties['tree'])) {
            $output['tree'] = $this->tree;
        }
        if (isset($this->_usedProperties['loggable'])) {
            $output['loggable'] = $this->loggable;
        }
        if (isset($this->_usedProperties['sortable'])) {
            $output['sortable'] = $this->sortable;
        }
        if (isset($this->_usedProperties['softdeleteable'])) {
            $output['softdeleteable'] = $this->softdeleteable;
        }
        if (isset($this->_usedProperties['uploadable'])) {
            $output['uploadable'] = $this->uploadable;
        }
        if (isset($this->_usedProperties['referenceIntegrity'])) {
            $output['reference_integrity'] = $this->referenceIntegrity;
        }

        return $output;
    }

}
