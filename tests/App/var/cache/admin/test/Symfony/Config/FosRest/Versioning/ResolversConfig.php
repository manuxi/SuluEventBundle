<?php

namespace Symfony\Config\FosRest\Versioning;

require_once __DIR__.\DIRECTORY_SEPARATOR.'Resolvers'.\DIRECTORY_SEPARATOR.'QueryConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Resolvers'.\DIRECTORY_SEPARATOR.'CustomHeaderConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Resolvers'.\DIRECTORY_SEPARATOR.'MediaTypeConfig.php';

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class ResolversConfig 
{
    private $query;
    private $customHeader;
    private $mediaType;
    private $_usedProperties = [];

    /**
     * @template TValue of array|bool
     * @param TValue $value
     * @default {"enabled":true,"parameter_name":"version"}
     * @return \Symfony\Config\FosRest\Versioning\Resolvers\QueryConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\FosRest\Versioning\Resolvers\QueryConfig : static)
     */
    public function query(array|bool $value = []): \Symfony\Config\FosRest\Versioning\Resolvers\QueryConfig|static
    {
        if (!\is_array($value)) {
            $this->_usedProperties['query'] = true;
            $this->query = $value;

            return $this;
        }

        if (!$this->query instanceof \Symfony\Config\FosRest\Versioning\Resolvers\QueryConfig) {
            $this->_usedProperties['query'] = true;
            $this->query = new \Symfony\Config\FosRest\Versioning\Resolvers\QueryConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "query()" has already been initialized. You cannot pass values the second time you call query().');
        }

        return $this->query;
    }

    /**
     * @template TValue of array|bool
     * @param TValue $value
     * @default {"enabled":true,"header_name":"X-Accept-Version"}
     * @return \Symfony\Config\FosRest\Versioning\Resolvers\CustomHeaderConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\FosRest\Versioning\Resolvers\CustomHeaderConfig : static)
     */
    public function customHeader(array|bool $value = []): \Symfony\Config\FosRest\Versioning\Resolvers\CustomHeaderConfig|static
    {
        if (!\is_array($value)) {
            $this->_usedProperties['customHeader'] = true;
            $this->customHeader = $value;

            return $this;
        }

        if (!$this->customHeader instanceof \Symfony\Config\FosRest\Versioning\Resolvers\CustomHeaderConfig) {
            $this->_usedProperties['customHeader'] = true;
            $this->customHeader = new \Symfony\Config\FosRest\Versioning\Resolvers\CustomHeaderConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "customHeader()" has already been initialized. You cannot pass values the second time you call customHeader().');
        }

        return $this->customHeader;
    }

    /**
     * @template TValue of array|bool
     * @param TValue $value
     * @default {"enabled":true,"regex":"\/(v|version)=(?P<version>[0-9\\.]+)\/"}
     * @return \Symfony\Config\FosRest\Versioning\Resolvers\MediaTypeConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\FosRest\Versioning\Resolvers\MediaTypeConfig : static)
     */
    public function mediaType(array|bool $value = []): \Symfony\Config\FosRest\Versioning\Resolvers\MediaTypeConfig|static
    {
        if (!\is_array($value)) {
            $this->_usedProperties['mediaType'] = true;
            $this->mediaType = $value;

            return $this;
        }

        if (!$this->mediaType instanceof \Symfony\Config\FosRest\Versioning\Resolvers\MediaTypeConfig) {
            $this->_usedProperties['mediaType'] = true;
            $this->mediaType = new \Symfony\Config\FosRest\Versioning\Resolvers\MediaTypeConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "mediaType()" has already been initialized. You cannot pass values the second time you call mediaType().');
        }

        return $this->mediaType;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('query', $config)) {
            $this->_usedProperties['query'] = true;
            $this->query = \is_array($config['query']) ? new \Symfony\Config\FosRest\Versioning\Resolvers\QueryConfig($config['query']) : $config['query'];
            unset($config['query']);
        }

        if (array_key_exists('custom_header', $config)) {
            $this->_usedProperties['customHeader'] = true;
            $this->customHeader = \is_array($config['custom_header']) ? new \Symfony\Config\FosRest\Versioning\Resolvers\CustomHeaderConfig($config['custom_header']) : $config['custom_header'];
            unset($config['custom_header']);
        }

        if (array_key_exists('media_type', $config)) {
            $this->_usedProperties['mediaType'] = true;
            $this->mediaType = \is_array($config['media_type']) ? new \Symfony\Config\FosRest\Versioning\Resolvers\MediaTypeConfig($config['media_type']) : $config['media_type'];
            unset($config['media_type']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['query'])) {
            $output['query'] = $this->query instanceof \Symfony\Config\FosRest\Versioning\Resolvers\QueryConfig ? $this->query->toArray() : $this->query;
        }
        if (isset($this->_usedProperties['customHeader'])) {
            $output['custom_header'] = $this->customHeader instanceof \Symfony\Config\FosRest\Versioning\Resolvers\CustomHeaderConfig ? $this->customHeader->toArray() : $this->customHeader;
        }
        if (isset($this->_usedProperties['mediaType'])) {
            $output['media_type'] = $this->mediaType instanceof \Symfony\Config\FosRest\Versioning\Resolvers\MediaTypeConfig ? $this->mediaType->toArray() : $this->mediaType;
        }

        return $output;
    }

}
