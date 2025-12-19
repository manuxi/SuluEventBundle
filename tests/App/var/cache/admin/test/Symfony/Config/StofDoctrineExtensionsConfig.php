<?php

namespace Symfony\Config;

require_once __DIR__.\DIRECTORY_SEPARATOR.'StofDoctrineExtensions'.\DIRECTORY_SEPARATOR.'OrmConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'StofDoctrineExtensions'.\DIRECTORY_SEPARATOR.'MongodbConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'StofDoctrineExtensions'.\DIRECTORY_SEPARATOR.'ClassConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'StofDoctrineExtensions'.\DIRECTORY_SEPARATOR.'SoftdeleteableConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'StofDoctrineExtensions'.\DIRECTORY_SEPARATOR.'UploadableConfig.php';

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Loader\ParamConfigurator;

/**
 * This class is automatically generated to help in creating a config.
 */
class StofDoctrineExtensionsConfig implements \Symfony\Component\Config\Builder\ConfigBuilderInterface
{
    private $orm;
    private $mongodb;
    private $class;
    private $softdeleteable;
    private $uploadable;
    private $defaultLocale;
    private $translationFallback;
    private $persistDefaultTranslation;
    private $skipTranslationOnLoad;
    private $metadataCachePool;
    private $_usedProperties = [];
    private $_hasDeprecatedCalls = false;

    /**
     * @deprecated since Symfony 7.4
     */
    public function orm(string $id, array $value = []): \Symfony\Config\StofDoctrineExtensions\OrmConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (!isset($this->orm[$id])) {
            $this->_usedProperties['orm'] = true;
            $this->orm[$id] = new \Symfony\Config\StofDoctrineExtensions\OrmConfig($value);
        } elseif (1 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "orm()" has already been initialized. You cannot pass values the second time you call orm().');
        }

        return $this->orm[$id];
    }

    /**
     * @deprecated since Symfony 7.4
     */
    public function mongodb(string $id, array $value = []): \Symfony\Config\StofDoctrineExtensions\MongodbConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (!isset($this->mongodb[$id])) {
            $this->_usedProperties['mongodb'] = true;
            $this->mongodb[$id] = new \Symfony\Config\StofDoctrineExtensions\MongodbConfig($value);
        } elseif (1 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "mongodb()" has already been initialized. You cannot pass values the second time you call mongodb().');
        }

        return $this->mongodb[$id];
    }

    /**
     * @default {"translatable":"Gedmo\\Translatable\\TranslatableListener","timestampable":"Gedmo\\Timestampable\\TimestampableListener","blameable":"Gedmo\\Blameable\\BlameableListener","sluggable":"Gedmo\\Sluggable\\SluggableListener","tree":"Gedmo\\Tree\\TreeListener","loggable":"Gedmo\\Loggable\\LoggableListener","sortable":"Gedmo\\Sortable\\SortableListener","softdeleteable":"Gedmo\\SoftDeleteable\\SoftDeleteableListener","uploadable":"Gedmo\\Uploadable\\UploadableListener","reference_integrity":"Gedmo\\ReferenceIntegrity\\ReferenceIntegrityListener"}
     * @deprecated since Symfony 7.4
     */
    public function class(array $value = []): \Symfony\Config\StofDoctrineExtensions\ClassConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (null === $this->class) {
            $this->_usedProperties['class'] = true;
            $this->class = new \Symfony\Config\StofDoctrineExtensions\ClassConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "class()" has already been initialized. You cannot pass values the second time you call class().');
        }

        return $this->class;
    }

    /**
     * @default {"handle_post_flush_event":false}
     * @deprecated since Symfony 7.4
     */
    public function softdeleteable(array $value = []): \Symfony\Config\StofDoctrineExtensions\SoftdeleteableConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (null === $this->softdeleteable) {
            $this->_usedProperties['softdeleteable'] = true;
            $this->softdeleteable = new \Symfony\Config\StofDoctrineExtensions\SoftdeleteableConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "softdeleteable()" has already been initialized. You cannot pass values the second time you call softdeleteable().');
        }

        return $this->softdeleteable;
    }

    /**
     * @default {"default_file_path":null,"mime_type_guesser_class":"Stof\\DoctrineExtensionsBundle\\Uploadable\\MimeTypeGuesserAdapter","default_file_info_class":"Stof\\DoctrineExtensionsBundle\\Uploadable\\UploadedFileInfo","validate_writable_directory":true}
     * @deprecated since Symfony 7.4
     */
    public function uploadable(array $value = []): \Symfony\Config\StofDoctrineExtensions\UploadableConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (null === $this->uploadable) {
            $this->_usedProperties['uploadable'] = true;
            $this->uploadable = new \Symfony\Config\StofDoctrineExtensions\UploadableConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "uploadable()" has already been initialized. You cannot pass values the second time you call uploadable().');
        }

        return $this->uploadable;
    }

    /**
     * @default 'en'
     * @param ParamConfigurator|mixed $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function defaultLocale($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['defaultLocale'] = true;
        $this->defaultLocale = $value;

        return $this;
    }

    /**
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function translationFallback($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['translationFallback'] = true;
        $this->translationFallback = $value;

        return $this;
    }

    /**
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function persistDefaultTranslation($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['persistDefaultTranslation'] = true;
        $this->persistDefaultTranslation = $value;

        return $this;
    }

    /**
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function skipTranslationOnLoad($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['skipTranslationOnLoad'] = true;
        $this->skipTranslationOnLoad = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function metadataCachePool($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['metadataCachePool'] = true;
        $this->metadataCachePool = $value;

        return $this;
    }

    public function getExtensionAlias(): string
    {
        return 'stof_doctrine_extensions';
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('orm', $config)) {
            $this->_usedProperties['orm'] = true;
            $this->orm = array_map(fn ($v) => new \Symfony\Config\StofDoctrineExtensions\OrmConfig($v), $config['orm']);
            unset($config['orm']);
        }

        if (array_key_exists('mongodb', $config)) {
            $this->_usedProperties['mongodb'] = true;
            $this->mongodb = array_map(fn ($v) => new \Symfony\Config\StofDoctrineExtensions\MongodbConfig($v), $config['mongodb']);
            unset($config['mongodb']);
        }

        if (array_key_exists('class', $config)) {
            $this->_usedProperties['class'] = true;
            $this->class = new \Symfony\Config\StofDoctrineExtensions\ClassConfig($config['class']);
            unset($config['class']);
        }

        if (array_key_exists('softdeleteable', $config)) {
            $this->_usedProperties['softdeleteable'] = true;
            $this->softdeleteable = new \Symfony\Config\StofDoctrineExtensions\SoftdeleteableConfig($config['softdeleteable']);
            unset($config['softdeleteable']);
        }

        if (array_key_exists('uploadable', $config)) {
            $this->_usedProperties['uploadable'] = true;
            $this->uploadable = new \Symfony\Config\StofDoctrineExtensions\UploadableConfig($config['uploadable']);
            unset($config['uploadable']);
        }

        if (array_key_exists('default_locale', $config)) {
            $this->_usedProperties['defaultLocale'] = true;
            $this->defaultLocale = $config['default_locale'];
            unset($config['default_locale']);
        }

        if (array_key_exists('translation_fallback', $config)) {
            $this->_usedProperties['translationFallback'] = true;
            $this->translationFallback = $config['translation_fallback'];
            unset($config['translation_fallback']);
        }

        if (array_key_exists('persist_default_translation', $config)) {
            $this->_usedProperties['persistDefaultTranslation'] = true;
            $this->persistDefaultTranslation = $config['persist_default_translation'];
            unset($config['persist_default_translation']);
        }

        if (array_key_exists('skip_translation_on_load', $config)) {
            $this->_usedProperties['skipTranslationOnLoad'] = true;
            $this->skipTranslationOnLoad = $config['skip_translation_on_load'];
            unset($config['skip_translation_on_load']);
        }

        if (array_key_exists('metadata_cache_pool', $config)) {
            $this->_usedProperties['metadataCachePool'] = true;
            $this->metadataCachePool = $config['metadata_cache_pool'];
            unset($config['metadata_cache_pool']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['orm'])) {
            $output['orm'] = array_map(fn ($v) => $v->toArray(), $this->orm);
        }
        if (isset($this->_usedProperties['mongodb'])) {
            $output['mongodb'] = array_map(fn ($v) => $v->toArray(), $this->mongodb);
        }
        if (isset($this->_usedProperties['class'])) {
            $output['class'] = $this->class->toArray();
        }
        if (isset($this->_usedProperties['softdeleteable'])) {
            $output['softdeleteable'] = $this->softdeleteable->toArray();
        }
        if (isset($this->_usedProperties['uploadable'])) {
            $output['uploadable'] = $this->uploadable->toArray();
        }
        if (isset($this->_usedProperties['defaultLocale'])) {
            $output['default_locale'] = $this->defaultLocale;
        }
        if (isset($this->_usedProperties['translationFallback'])) {
            $output['translation_fallback'] = $this->translationFallback;
        }
        if (isset($this->_usedProperties['persistDefaultTranslation'])) {
            $output['persist_default_translation'] = $this->persistDefaultTranslation;
        }
        if (isset($this->_usedProperties['skipTranslationOnLoad'])) {
            $output['skip_translation_on_load'] = $this->skipTranslationOnLoad;
        }
        if (isset($this->_usedProperties['metadataCachePool'])) {
            $output['metadata_cache_pool'] = $this->metadataCachePool;
        }
        if ($this->_hasDeprecatedCalls) {
            trigger_deprecation('symfony/config', '7.4', 'Calling any fluent method on "%s" is deprecated; pass the configuration to the constructor instead.', $this::class);
        }

        return $output;
    }

}
