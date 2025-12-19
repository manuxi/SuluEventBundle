<?php

namespace Symfony\Config\SuluMedia;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class RoutingConfig 
{
    private $mediaProxyPath;
    private $mediaDownloadPath;
    private $mediaDownloadPathAdmin;
    private $_usedProperties = [];

    /**
     * @default '/uploads/media/{slug}'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function mediaProxyPath($value): static
    {
        $this->_usedProperties['mediaProxyPath'] = true;
        $this->mediaProxyPath = $value;

        return $this;
    }

    /**
     * @default '/media/{id}/download/{slug}'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function mediaDownloadPath($value): static
    {
        $this->_usedProperties['mediaDownloadPath'] = true;
        $this->mediaDownloadPath = $value;

        return $this;
    }

    /**
     * @default '/admin/media/{id}/download/{slug}'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function mediaDownloadPathAdmin($value): static
    {
        $this->_usedProperties['mediaDownloadPathAdmin'] = true;
        $this->mediaDownloadPathAdmin = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('media_proxy_path', $config)) {
            $this->_usedProperties['mediaProxyPath'] = true;
            $this->mediaProxyPath = $config['media_proxy_path'];
            unset($config['media_proxy_path']);
        }

        if (array_key_exists('media_download_path', $config)) {
            $this->_usedProperties['mediaDownloadPath'] = true;
            $this->mediaDownloadPath = $config['media_download_path'];
            unset($config['media_download_path']);
        }

        if (array_key_exists('media_download_path_admin', $config)) {
            $this->_usedProperties['mediaDownloadPathAdmin'] = true;
            $this->mediaDownloadPathAdmin = $config['media_download_path_admin'];
            unset($config['media_download_path_admin']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['mediaProxyPath'])) {
            $output['media_proxy_path'] = $this->mediaProxyPath;
        }
        if (isset($this->_usedProperties['mediaDownloadPath'])) {
            $output['media_download_path'] = $this->mediaDownloadPath;
        }
        if (isset($this->_usedProperties['mediaDownloadPathAdmin'])) {
            $output['media_download_path_admin'] = $this->mediaDownloadPathAdmin;
        }

        return $output;
    }

}
