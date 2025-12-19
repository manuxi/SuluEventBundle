<?php

namespace Symfony\Config\Doctrine\Orm\EntityManagerConfig;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class MappingConfig 
{
    private $mapping;
    private $type;
    private $dir;
    private $alias;
    private $prefix;
    private $isBundle;
    private $_usedProperties = [];

    /**
     * @default true
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function mapping($value): static
    {
        $this->_usedProperties['mapping'] = true;
        $this->mapping = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function type($value): static
    {
        $this->_usedProperties['type'] = true;
        $this->type = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function dir($value): static
    {
        $this->_usedProperties['dir'] = true;
        $this->dir = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function alias($value): static
    {
        $this->_usedProperties['alias'] = true;
        $this->alias = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function prefix($value): static
    {
        $this->_usedProperties['prefix'] = true;
        $this->prefix = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function isBundle($value): static
    {
        $this->_usedProperties['isBundle'] = true;
        $this->isBundle = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('mapping', $config)) {
            $this->_usedProperties['mapping'] = true;
            $this->mapping = $config['mapping'];
            unset($config['mapping']);
        }

        if (array_key_exists('type', $config)) {
            $this->_usedProperties['type'] = true;
            $this->type = $config['type'];
            unset($config['type']);
        }

        if (array_key_exists('dir', $config)) {
            $this->_usedProperties['dir'] = true;
            $this->dir = $config['dir'];
            unset($config['dir']);
        }

        if (array_key_exists('alias', $config)) {
            $this->_usedProperties['alias'] = true;
            $this->alias = $config['alias'];
            unset($config['alias']);
        }

        if (array_key_exists('prefix', $config)) {
            $this->_usedProperties['prefix'] = true;
            $this->prefix = $config['prefix'];
            unset($config['prefix']);
        }

        if (array_key_exists('is_bundle', $config)) {
            $this->_usedProperties['isBundle'] = true;
            $this->isBundle = $config['is_bundle'];
            unset($config['is_bundle']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['mapping'])) {
            $output['mapping'] = $this->mapping;
        }
        if (isset($this->_usedProperties['type'])) {
            $output['type'] = $this->type;
        }
        if (isset($this->_usedProperties['dir'])) {
            $output['dir'] = $this->dir;
        }
        if (isset($this->_usedProperties['alias'])) {
            $output['alias'] = $this->alias;
        }
        if (isset($this->_usedProperties['prefix'])) {
            $output['prefix'] = $this->prefix;
        }
        if (isset($this->_usedProperties['isBundle'])) {
            $output['is_bundle'] = $this->isBundle;
        }

        return $output;
    }

}
