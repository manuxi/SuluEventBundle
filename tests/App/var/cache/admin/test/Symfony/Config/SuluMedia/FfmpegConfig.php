<?php

namespace Symfony\Config\SuluMedia;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class FfmpegConfig 
{
    private $ffmpegBinary;
    private $ffprobeBinary;
    private $binaryTimeout;
    private $threadsCount;
    private $_usedProperties = [];

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function ffmpegBinary($value): static
    {
        $this->_usedProperties['ffmpegBinary'] = true;
        $this->ffmpegBinary = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function ffprobeBinary($value): static
    {
        $this->_usedProperties['ffprobeBinary'] = true;
        $this->ffprobeBinary = $value;

        return $this;
    }

    /**
     * @default 60
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function binaryTimeout($value): static
    {
        $this->_usedProperties['binaryTimeout'] = true;
        $this->binaryTimeout = $value;

        return $this;
    }

    /**
     * @default 4
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function threadsCount($value): static
    {
        $this->_usedProperties['threadsCount'] = true;
        $this->threadsCount = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('ffmpeg_binary', $config)) {
            $this->_usedProperties['ffmpegBinary'] = true;
            $this->ffmpegBinary = $config['ffmpeg_binary'];
            unset($config['ffmpeg_binary']);
        }

        if (array_key_exists('ffprobe_binary', $config)) {
            $this->_usedProperties['ffprobeBinary'] = true;
            $this->ffprobeBinary = $config['ffprobe_binary'];
            unset($config['ffprobe_binary']);
        }

        if (array_key_exists('binary_timeout', $config)) {
            $this->_usedProperties['binaryTimeout'] = true;
            $this->binaryTimeout = $config['binary_timeout'];
            unset($config['binary_timeout']);
        }

        if (array_key_exists('threads_count', $config)) {
            $this->_usedProperties['threadsCount'] = true;
            $this->threadsCount = $config['threads_count'];
            unset($config['threads_count']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['ffmpegBinary'])) {
            $output['ffmpeg_binary'] = $this->ffmpegBinary;
        }
        if (isset($this->_usedProperties['ffprobeBinary'])) {
            $output['ffprobe_binary'] = $this->ffprobeBinary;
        }
        if (isset($this->_usedProperties['binaryTimeout'])) {
            $output['binary_timeout'] = $this->binaryTimeout;
        }
        if (isset($this->_usedProperties['threadsCount'])) {
            $output['threads_count'] = $this->threadsCount;
        }

        return $output;
    }

}
