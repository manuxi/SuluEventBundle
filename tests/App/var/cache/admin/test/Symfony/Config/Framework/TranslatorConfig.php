<?php

namespace Symfony\Config\Framework;

require_once __DIR__.\DIRECTORY_SEPARATOR.'Translator'.\DIRECTORY_SEPARATOR.'PseudoLocalizationConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Translator'.\DIRECTORY_SEPARATOR.'ProviderConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Translator'.\DIRECTORY_SEPARATOR.'GlobalConfig.php';

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class TranslatorConfig 
{
    private $enabled;
    private $fallbacks;
    private $logging;
    private $formatter;
    private $cacheDir;
    private $defaultPath;
    private $paths;
    private $pseudoLocalization;
    private $providers;
    private $globals;
    private $_usedProperties = [];

    /**
     * @default true
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function enabled($value): static
    {
        $this->_usedProperties['enabled'] = true;
        $this->enabled = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed>|mixed $value
     *
     * @return $this
     */
    public function fallbacks(mixed $value): static
    {
        $this->_usedProperties['fallbacks'] = true;
        $this->fallbacks = $value;

        return $this;
    }

    /**
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function logging($value): static
    {
        $this->_usedProperties['logging'] = true;
        $this->logging = $value;

        return $this;
    }

    /**
     * @default 'translator.formatter.default'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function formatter($value): static
    {
        $this->_usedProperties['formatter'] = true;
        $this->formatter = $value;

        return $this;
    }

    /**
     * @default '%kernel.cache_dir%/translations'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function cacheDir($value): static
    {
        $this->_usedProperties['cacheDir'] = true;
        $this->cacheDir = $value;

        return $this;
    }

    /**
     * The default path used to load translations.
     * @default '%kernel.project_dir%/translations'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function defaultPath($value): static
    {
        $this->_usedProperties['defaultPath'] = true;
        $this->defaultPath = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed> $value
     *
     * @return $this
     */
    public function paths(ParamConfigurator|array $value): static
    {
        $this->_usedProperties['paths'] = true;
        $this->paths = $value;

        return $this;
    }

    /**
     * @template TValue of array|bool
     * @param TValue $value
     * @default {"enabled":false,"accents":true,"expansion_factor":1,"brackets":true,"parse_html":false,"localizable_html_attributes":[]}
     * @return \Symfony\Config\Framework\Translator\PseudoLocalizationConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\Framework\Translator\PseudoLocalizationConfig : static)
     */
    public function pseudoLocalization(array|bool $value = []): \Symfony\Config\Framework\Translator\PseudoLocalizationConfig|static
    {
        if (!\is_array($value)) {
            $this->_usedProperties['pseudoLocalization'] = true;
            $this->pseudoLocalization = $value;

            return $this;
        }

        if (!$this->pseudoLocalization instanceof \Symfony\Config\Framework\Translator\PseudoLocalizationConfig) {
            $this->_usedProperties['pseudoLocalization'] = true;
            $this->pseudoLocalization = new \Symfony\Config\Framework\Translator\PseudoLocalizationConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "pseudoLocalization()" has already been initialized. You cannot pass values the second time you call pseudoLocalization().');
        }

        return $this->pseudoLocalization;
    }

    /**
     * Translation providers you can read/write your translations from.
     */
    public function provider(string $name, array $value = []): \Symfony\Config\Framework\Translator\ProviderConfig
    {
        if (!isset($this->providers[$name])) {
            $this->_usedProperties['providers'] = true;
            $this->providers[$name] = new \Symfony\Config\Framework\Translator\ProviderConfig($value);
        } elseif (1 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "provider()" has already been initialized. You cannot pass values the second time you call provider().');
        }

        return $this->providers[$name];
    }

    /**
     * @template TValue of mixed
     * @param TValue $value
     * Global parameters.
     * @example 3.14
     * @return \Symfony\Config\Framework\Translator\GlobalConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\Framework\Translator\GlobalConfig : static)
     */
    public function global(string $name, mixed $value = []): \Symfony\Config\Framework\Translator\GlobalConfig|static
    {
        if (!\is_array($value)) {
            $this->_usedProperties['globals'] = true;
            $this->globals[$name] = $value;

            return $this;
        }

        if (!isset($this->globals[$name]) || !$this->globals[$name] instanceof \Symfony\Config\Framework\Translator\GlobalConfig) {
            $this->_usedProperties['globals'] = true;
            $this->globals[$name] = new \Symfony\Config\Framework\Translator\GlobalConfig($value);
        } elseif (1 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "global()" has already been initialized. You cannot pass values the second time you call global().');
        }

        return $this->globals[$name];
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('enabled', $config)) {
            $this->_usedProperties['enabled'] = true;
            $this->enabled = $config['enabled'];
            unset($config['enabled']);
        }

        if (array_key_exists('fallbacks', $config)) {
            $this->_usedProperties['fallbacks'] = true;
            $this->fallbacks = $config['fallbacks'];
            unset($config['fallbacks']);
        }

        if (array_key_exists('logging', $config)) {
            $this->_usedProperties['logging'] = true;
            $this->logging = $config['logging'];
            unset($config['logging']);
        }

        if (array_key_exists('formatter', $config)) {
            $this->_usedProperties['formatter'] = true;
            $this->formatter = $config['formatter'];
            unset($config['formatter']);
        }

        if (array_key_exists('cache_dir', $config)) {
            $this->_usedProperties['cacheDir'] = true;
            $this->cacheDir = $config['cache_dir'];
            unset($config['cache_dir']);
        }

        if (array_key_exists('default_path', $config)) {
            $this->_usedProperties['defaultPath'] = true;
            $this->defaultPath = $config['default_path'];
            unset($config['default_path']);
        }

        if (array_key_exists('paths', $config)) {
            $this->_usedProperties['paths'] = true;
            $this->paths = $config['paths'];
            unset($config['paths']);
        }

        if (array_key_exists('pseudo_localization', $config)) {
            $this->_usedProperties['pseudoLocalization'] = true;
            $this->pseudoLocalization = \is_array($config['pseudo_localization']) ? new \Symfony\Config\Framework\Translator\PseudoLocalizationConfig($config['pseudo_localization']) : $config['pseudo_localization'];
            unset($config['pseudo_localization']);
        }

        if (array_key_exists('providers', $config)) {
            $this->_usedProperties['providers'] = true;
            $this->providers = array_map(fn ($v) => new \Symfony\Config\Framework\Translator\ProviderConfig($v), $config['providers']);
            unset($config['providers']);
        }

        if (array_key_exists('globals', $config)) {
            $this->_usedProperties['globals'] = true;
            $this->globals = array_map(fn ($v) => \is_array($v) ? new \Symfony\Config\Framework\Translator\GlobalConfig($v) : $v, $config['globals']);
            unset($config['globals']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['enabled'])) {
            $output['enabled'] = $this->enabled;
        }
        if (isset($this->_usedProperties['fallbacks'])) {
            $output['fallbacks'] = $this->fallbacks;
        }
        if (isset($this->_usedProperties['logging'])) {
            $output['logging'] = $this->logging;
        }
        if (isset($this->_usedProperties['formatter'])) {
            $output['formatter'] = $this->formatter;
        }
        if (isset($this->_usedProperties['cacheDir'])) {
            $output['cache_dir'] = $this->cacheDir;
        }
        if (isset($this->_usedProperties['defaultPath'])) {
            $output['default_path'] = $this->defaultPath;
        }
        if (isset($this->_usedProperties['paths'])) {
            $output['paths'] = $this->paths;
        }
        if (isset($this->_usedProperties['pseudoLocalization'])) {
            $output['pseudo_localization'] = $this->pseudoLocalization instanceof \Symfony\Config\Framework\Translator\PseudoLocalizationConfig ? $this->pseudoLocalization->toArray() : $this->pseudoLocalization;
        }
        if (isset($this->_usedProperties['providers'])) {
            $output['providers'] = array_map(fn ($v) => $v->toArray(), $this->providers);
        }
        if (isset($this->_usedProperties['globals'])) {
            $output['globals'] = array_map(fn ($v) => $v instanceof \Symfony\Config\Framework\Translator\GlobalConfig ? $v->toArray() : $v, $this->globals);
        }

        return $output;
    }

}
