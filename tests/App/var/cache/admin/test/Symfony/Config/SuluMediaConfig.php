<?php

namespace Symfony\Config;

require_once __DIR__.\DIRECTORY_SEPARATOR.'SuluMedia'.\DIRECTORY_SEPARATOR.'SystemCollectionsConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'SuluMedia'.\DIRECTORY_SEPARATOR.'GhostScriptConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'SuluMedia'.\DIRECTORY_SEPARATOR.'UploadConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'SuluMedia'.\DIRECTORY_SEPARATOR.'FormatManagerConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'SuluMedia'.\DIRECTORY_SEPARATOR.'FormatCacheConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'SuluMedia'.\DIRECTORY_SEPARATOR.'DispositionTypeConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'SuluMedia'.\DIRECTORY_SEPARATOR.'RoutingConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'SuluMedia'.\DIRECTORY_SEPARATOR.'FfmpegConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'SuluMedia'.\DIRECTORY_SEPARATOR.'ObjectsConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'SuluMedia'.\DIRECTORY_SEPARATOR.'StorageConfig.php';

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class SuluMediaConfig implements \Symfony\Component\Config\Builder\ConfigBuilderInterface
{
    private $adapter;
    private $imageFormatFiles;
    private $systemCollections;
    private $ghostScript;
    private $upload;
    private $formatManager;
    private $formatCache;
    private $dispositionType;
    private $routing;
    private $ffmpeg;
    private $objects;
    private $storage;
    private $_usedProperties = [];
    private $_hasDeprecatedCalls = false;

    /**
     * @default 'auto'
     * @param ParamConfigurator|mixed $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function adapter($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['adapter'] = true;
        $this->adapter = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed> $value
     *
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function imageFormatFiles(ParamConfigurator|array $value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['imageFormatFiles'] = true;
        $this->imageFormatFiles = $value;

        return $this;
    }

    /**
     * @deprecated since Symfony 7.4
     */
    public function systemCollections(string $key, array $value = []): \Symfony\Config\SuluMedia\SystemCollectionsConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (!isset($this->systemCollections[$key])) {
            $this->_usedProperties['systemCollections'] = true;
            $this->systemCollections[$key] = new \Symfony\Config\SuluMedia\SystemCollectionsConfig($value);
        } elseif (1 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "systemCollections()" has already been initialized. You cannot pass values the second time you call systemCollections().');
        }

        return $this->systemCollections[$key];
    }

    /**
     * @default {"path":"gs"}
     * @deprecated since Symfony 7.4
     */
    public function ghostScript(array $value = []): \Symfony\Config\SuluMedia\GhostScriptConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (null === $this->ghostScript) {
            $this->_usedProperties['ghostScript'] = true;
            $this->ghostScript = new \Symfony\Config\SuluMedia\GhostScriptConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "ghostScript()" has already been initialized. You cannot pass values the second time you call ghostScript().');
        }

        return $this->ghostScript;
    }

    /**
     * @default {"max_filesize":256,"blocked_file_types":[]}
     * @deprecated since Symfony 7.4
     */
    public function upload(array $value = []): \Symfony\Config\SuluMedia\UploadConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (null === $this->upload) {
            $this->_usedProperties['upload'] = true;
            $this->upload = new \Symfony\Config\SuluMedia\UploadConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "upload()" has already been initialized. You cannot pass values the second time you call upload().');
        }

        return $this->upload;
    }

    /**
     * @default {"response_headers":{"Cache-Control":"public, immutable, max-age=31536000"},"default_imagine_options":[],"mime_types":[],"types":[{"type":"document","mimeTypes":["*"]},{"type":"image","mimeTypes":["image\/*"]},{"type":"video","mimeTypes":["video\/*"]},{"type":"audio","mimeTypes":["audio\/*"]}]}
     * @deprecated since Symfony 7.4
     */
    public function formatManager(array $value = []): \Symfony\Config\SuluMedia\FormatManagerConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (null === $this->formatManager) {
            $this->_usedProperties['formatManager'] = true;
            $this->formatManager = new \Symfony\Config\SuluMedia\FormatManagerConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "formatManager()" has already been initialized. You cannot pass values the second time you call formatManager().');
        }

        return $this->formatManager;
    }

    /**
     * @default {"path":"%kernel.project_dir%\/public\/uploads\/media","save_image":true,"segments":10}
     * @deprecated since Symfony 7.4
     */
    public function formatCache(array $value = []): \Symfony\Config\SuluMedia\FormatCacheConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (null === $this->formatCache) {
            $this->_usedProperties['formatCache'] = true;
            $this->formatCache = new \Symfony\Config\SuluMedia\FormatCacheConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "formatCache()" has already been initialized. You cannot pass values the second time you call formatCache().');
        }

        return $this->formatCache;
    }

    /**
     * @default {"default":"attachment","mime_types_inline":[],"mime_types_attachment":[]}
     * @deprecated since Symfony 7.4
     */
    public function dispositionType(array $value = []): \Symfony\Config\SuluMedia\DispositionTypeConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (null === $this->dispositionType) {
            $this->_usedProperties['dispositionType'] = true;
            $this->dispositionType = new \Symfony\Config\SuluMedia\DispositionTypeConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "dispositionType()" has already been initialized. You cannot pass values the second time you call dispositionType().');
        }

        return $this->dispositionType;
    }

    /**
     * @default {"media_proxy_path":"\/uploads\/media\/{slug}","media_download_path":"\/media\/{id}\/download\/{slug}","media_download_path_admin":"\/admin\/media\/{id}\/download\/{slug}"}
     * @deprecated since Symfony 7.4
     */
    public function routing(array $value = []): \Symfony\Config\SuluMedia\RoutingConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (null === $this->routing) {
            $this->_usedProperties['routing'] = true;
            $this->routing = new \Symfony\Config\SuluMedia\RoutingConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "routing()" has already been initialized. You cannot pass values the second time you call routing().');
        }

        return $this->routing;
    }

    /**
     * @deprecated since Symfony 7.4
     */
    public function ffmpeg(array $value = []): \Symfony\Config\SuluMedia\FfmpegConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (null === $this->ffmpeg) {
            $this->_usedProperties['ffmpeg'] = true;
            $this->ffmpeg = new \Symfony\Config\SuluMedia\FfmpegConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "ffmpeg()" has already been initialized. You cannot pass values the second time you call ffmpeg().');
        }

        return $this->ffmpeg;
    }

    /**
     * @default {"media":{"model":"Sulu\\Bundle\\MediaBundle\\Entity\\Media","repository":"Sulu\\Bundle\\MediaBundle\\Entity\\MediaRepository"}}
     * @deprecated since Symfony 7.4
     */
    public function objects(array $value = []): \Symfony\Config\SuluMedia\ObjectsConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (null === $this->objects) {
            $this->_usedProperties['objects'] = true;
            $this->objects = new \Symfony\Config\SuluMedia\ObjectsConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "objects()" has already been initialized. You cannot pass values the second time you call objects().');
        }

        return $this->objects;
    }

    /**
     * @default {"flysystem_service":"default.storage","segments":10}
     * @deprecated since Symfony 7.4
     */
    public function storage(array $value = []): \Symfony\Config\SuluMedia\StorageConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (null === $this->storage) {
            $this->_usedProperties['storage'] = true;
            $this->storage = new \Symfony\Config\SuluMedia\StorageConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "storage()" has already been initialized. You cannot pass values the second time you call storage().');
        }

        return $this->storage;
    }

    public function getExtensionAlias(): string
    {
        return 'sulu_media';
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('adapter', $config)) {
            $this->_usedProperties['adapter'] = true;
            $this->adapter = $config['adapter'];
            unset($config['adapter']);
        }

        if (array_key_exists('image_format_files', $config)) {
            $this->_usedProperties['imageFormatFiles'] = true;
            $this->imageFormatFiles = $config['image_format_files'];
            unset($config['image_format_files']);
        }

        if (array_key_exists('system_collections', $config)) {
            $this->_usedProperties['systemCollections'] = true;
            $this->systemCollections = array_map(fn ($v) => new \Symfony\Config\SuluMedia\SystemCollectionsConfig($v), $config['system_collections']);
            unset($config['system_collections']);
        }

        if (array_key_exists('ghost_script', $config)) {
            $this->_usedProperties['ghostScript'] = true;
            $this->ghostScript = new \Symfony\Config\SuluMedia\GhostScriptConfig($config['ghost_script']);
            unset($config['ghost_script']);
        }

        if (array_key_exists('upload', $config)) {
            $this->_usedProperties['upload'] = true;
            $this->upload = new \Symfony\Config\SuluMedia\UploadConfig($config['upload']);
            unset($config['upload']);
        }

        if (array_key_exists('format_manager', $config)) {
            $this->_usedProperties['formatManager'] = true;
            $this->formatManager = new \Symfony\Config\SuluMedia\FormatManagerConfig($config['format_manager']);
            unset($config['format_manager']);
        }

        if (array_key_exists('format_cache', $config)) {
            $this->_usedProperties['formatCache'] = true;
            $this->formatCache = new \Symfony\Config\SuluMedia\FormatCacheConfig($config['format_cache']);
            unset($config['format_cache']);
        }

        if (array_key_exists('disposition_type', $config)) {
            $this->_usedProperties['dispositionType'] = true;
            $this->dispositionType = new \Symfony\Config\SuluMedia\DispositionTypeConfig($config['disposition_type']);
            unset($config['disposition_type']);
        }

        if (array_key_exists('routing', $config)) {
            $this->_usedProperties['routing'] = true;
            $this->routing = new \Symfony\Config\SuluMedia\RoutingConfig($config['routing']);
            unset($config['routing']);
        }

        if (array_key_exists('ffmpeg', $config)) {
            $this->_usedProperties['ffmpeg'] = true;
            $this->ffmpeg = new \Symfony\Config\SuluMedia\FfmpegConfig($config['ffmpeg']);
            unset($config['ffmpeg']);
        }

        if (array_key_exists('objects', $config)) {
            $this->_usedProperties['objects'] = true;
            $this->objects = new \Symfony\Config\SuluMedia\ObjectsConfig($config['objects']);
            unset($config['objects']);
        }

        if (array_key_exists('storage', $config)) {
            $this->_usedProperties['storage'] = true;
            $this->storage = new \Symfony\Config\SuluMedia\StorageConfig($config['storage']);
            unset($config['storage']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['adapter'])) {
            $output['adapter'] = $this->adapter;
        }
        if (isset($this->_usedProperties['imageFormatFiles'])) {
            $output['image_format_files'] = $this->imageFormatFiles;
        }
        if (isset($this->_usedProperties['systemCollections'])) {
            $output['system_collections'] = array_map(fn ($v) => $v->toArray(), $this->systemCollections);
        }
        if (isset($this->_usedProperties['ghostScript'])) {
            $output['ghost_script'] = $this->ghostScript->toArray();
        }
        if (isset($this->_usedProperties['upload'])) {
            $output['upload'] = $this->upload->toArray();
        }
        if (isset($this->_usedProperties['formatManager'])) {
            $output['format_manager'] = $this->formatManager->toArray();
        }
        if (isset($this->_usedProperties['formatCache'])) {
            $output['format_cache'] = $this->formatCache->toArray();
        }
        if (isset($this->_usedProperties['dispositionType'])) {
            $output['disposition_type'] = $this->dispositionType->toArray();
        }
        if (isset($this->_usedProperties['routing'])) {
            $output['routing'] = $this->routing->toArray();
        }
        if (isset($this->_usedProperties['ffmpeg'])) {
            $output['ffmpeg'] = $this->ffmpeg->toArray();
        }
        if (isset($this->_usedProperties['objects'])) {
            $output['objects'] = $this->objects->toArray();
        }
        if (isset($this->_usedProperties['storage'])) {
            $output['storage'] = $this->storage->toArray();
        }
        if ($this->_hasDeprecatedCalls) {
            trigger_deprecation('symfony/config', '7.4', 'Calling any fluent method on "%s" is deprecated; pass the configuration to the constructor instead.', $this::class);
        }

        return $output;
    }

}
