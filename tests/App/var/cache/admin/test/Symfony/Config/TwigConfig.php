<?php

namespace Symfony\Config;

require_once __DIR__.\DIRECTORY_SEPARATOR.'Twig'.\DIRECTORY_SEPARATOR.'GlobalConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Twig'.\DIRECTORY_SEPARATOR.'DateConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Twig'.\DIRECTORY_SEPARATOR.'NumberFormatConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Twig'.\DIRECTORY_SEPARATOR.'MailerConfig.php';

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class TwigConfig implements \Symfony\Component\Config\Builder\ConfigBuilderInterface
{
    private $formThemes;
    private $globals;
    private $autoescapeService;
    private $autoescapeServiceMethod;
    private $baseTemplateClass;
    private $cache;
    private $charset;
    private $debug;
    private $strictVariables;
    private $autoReload;
    private $optimizations;
    private $defaultPath;
    private $fileNamePattern;
    private $paths;
    private $date;
    private $numberFormat;
    private $mailer;
    private $_usedProperties = [];
    private $_hasDeprecatedCalls = false;

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed> $value
     *
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function formThemes(ParamConfigurator|array $value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['formThemes'] = true;
        $this->formThemes = $value;

        return $this;
    }

    /**
     * @template TValue of mixed
     * @param TValue $value
     * @example "@bar"
     * @example 3.14
     * @return \Symfony\Config\Twig\GlobalConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\Twig\GlobalConfig : static)
     * @deprecated since Symfony 7.4
     */
    public function global(string $key, mixed $value = []): \Symfony\Config\Twig\GlobalConfig|static
    {
        $this->_hasDeprecatedCalls = true;
        if (!\is_array($value)) {
            $this->_usedProperties['globals'] = true;
            $this->globals[$key] = $value;

            return $this;
        }

        if (!isset($this->globals[$key]) || !$this->globals[$key] instanceof \Symfony\Config\Twig\GlobalConfig) {
            $this->_usedProperties['globals'] = true;
            $this->globals[$key] = new \Symfony\Config\Twig\GlobalConfig($value);
        } elseif (1 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "global()" has already been initialized. You cannot pass values the second time you call global().');
        }

        return $this->globals[$key];
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function autoescapeService($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['autoescapeService'] = true;
        $this->autoescapeService = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function autoescapeServiceMethod($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['autoescapeServiceMethod'] = true;
        $this->autoescapeServiceMethod = $value;

        return $this;
    }

    /**
     * @example Twig\Template
     * @default null
     * @param ParamConfigurator|mixed $value
     * @deprecated Since symfony/twig-bundle 7.1: The child node "base_template_class" at path "twig" is deprecated.
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function baseTemplateClass($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['baseTemplateClass'] = true;
        $this->baseTemplateClass = $value;

        return $this;
    }

    /**
     * @default true
     * @param ParamConfigurator|mixed $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function cache($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['cache'] = true;
        $this->cache = $value;

        return $this;
    }

    /**
     * @default '%kernel.charset%'
     * @param ParamConfigurator|mixed $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function charset($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['charset'] = true;
        $this->charset = $value;

        return $this;
    }

    /**
     * @default '%kernel.debug%'
     * @param ParamConfigurator|bool $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function debug($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['debug'] = true;
        $this->debug = $value;

        return $this;
    }

    /**
     * @default '%kernel.debug%'
     * @param ParamConfigurator|bool $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function strictVariables($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['strictVariables'] = true;
        $this->strictVariables = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function autoReload($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['autoReload'] = true;
        $this->autoReload = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|int $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function optimizations($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['optimizations'] = true;
        $this->optimizations = $value;

        return $this;
    }

    /**
     * The default path used to load templates.
     * @default '%kernel.project_dir%/templates'
     * @param ParamConfigurator|mixed $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function defaultPath($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['defaultPath'] = true;
        $this->defaultPath = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed>|string $value
     *
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function fileNamePattern(ParamConfigurator|string|array $value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['fileNamePattern'] = true;
        $this->fileNamePattern = $value;

        return $this;
    }

    /**
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function path(string $paths, mixed $value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['paths'] = true;
        $this->paths[$paths] = $value;

        return $this;
    }

    /**
     * The default format options used by the date filter.
     * @default {"format":"F j, Y H:i","interval_format":"%d days","timezone":null}
     * @deprecated since Symfony 7.4
     */
    public function date(array $value = []): \Symfony\Config\Twig\DateConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (null === $this->date) {
            $this->_usedProperties['date'] = true;
            $this->date = new \Symfony\Config\Twig\DateConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "date()" has already been initialized. You cannot pass values the second time you call date().');
        }

        return $this->date;
    }

    /**
     * The default format options for the number_format filter.
     * @default {"decimals":0,"decimal_point":".","thousands_separator":","}
     * @deprecated since Symfony 7.4
     */
    public function numberFormat(array $value = []): \Symfony\Config\Twig\NumberFormatConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (null === $this->numberFormat) {
            $this->_usedProperties['numberFormat'] = true;
            $this->numberFormat = new \Symfony\Config\Twig\NumberFormatConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "numberFormat()" has already been initialized. You cannot pass values the second time you call numberFormat().');
        }

        return $this->numberFormat;
    }

    /**
     * @deprecated since Symfony 7.4
     */
    public function mailer(array $value = []): \Symfony\Config\Twig\MailerConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (null === $this->mailer) {
            $this->_usedProperties['mailer'] = true;
            $this->mailer = new \Symfony\Config\Twig\MailerConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "mailer()" has already been initialized. You cannot pass values the second time you call mailer().');
        }

        return $this->mailer;
    }

    public function getExtensionAlias(): string
    {
        return 'twig';
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('form_themes', $config)) {
            $this->_usedProperties['formThemes'] = true;
            $this->formThemes = $config['form_themes'];
            unset($config['form_themes']);
        }

        if (array_key_exists('globals', $config)) {
            $this->_usedProperties['globals'] = true;
            $this->globals = array_map(fn ($v) => \is_array($v) ? new \Symfony\Config\Twig\GlobalConfig($v) : $v, $config['globals']);
            unset($config['globals']);
        }

        if (array_key_exists('autoescape_service', $config)) {
            $this->_usedProperties['autoescapeService'] = true;
            $this->autoescapeService = $config['autoescape_service'];
            unset($config['autoescape_service']);
        }

        if (array_key_exists('autoescape_service_method', $config)) {
            $this->_usedProperties['autoescapeServiceMethod'] = true;
            $this->autoescapeServiceMethod = $config['autoescape_service_method'];
            unset($config['autoescape_service_method']);
        }

        if (array_key_exists('base_template_class', $config)) {
            $this->_usedProperties['baseTemplateClass'] = true;
            $this->baseTemplateClass = $config['base_template_class'];
            unset($config['base_template_class']);
        }

        if (array_key_exists('cache', $config)) {
            $this->_usedProperties['cache'] = true;
            $this->cache = $config['cache'];
            unset($config['cache']);
        }

        if (array_key_exists('charset', $config)) {
            $this->_usedProperties['charset'] = true;
            $this->charset = $config['charset'];
            unset($config['charset']);
        }

        if (array_key_exists('debug', $config)) {
            $this->_usedProperties['debug'] = true;
            $this->debug = $config['debug'];
            unset($config['debug']);
        }

        if (array_key_exists('strict_variables', $config)) {
            $this->_usedProperties['strictVariables'] = true;
            $this->strictVariables = $config['strict_variables'];
            unset($config['strict_variables']);
        }

        if (array_key_exists('auto_reload', $config)) {
            $this->_usedProperties['autoReload'] = true;
            $this->autoReload = $config['auto_reload'];
            unset($config['auto_reload']);
        }

        if (array_key_exists('optimizations', $config)) {
            $this->_usedProperties['optimizations'] = true;
            $this->optimizations = $config['optimizations'];
            unset($config['optimizations']);
        }

        if (array_key_exists('default_path', $config)) {
            $this->_usedProperties['defaultPath'] = true;
            $this->defaultPath = $config['default_path'];
            unset($config['default_path']);
        }

        if (array_key_exists('file_name_pattern', $config)) {
            $this->_usedProperties['fileNamePattern'] = true;
            $this->fileNamePattern = $config['file_name_pattern'];
            unset($config['file_name_pattern']);
        }

        if (array_key_exists('paths', $config)) {
            $this->_usedProperties['paths'] = true;
            $this->paths = $config['paths'];
            unset($config['paths']);
        }

        if (array_key_exists('date', $config)) {
            $this->_usedProperties['date'] = true;
            $this->date = new \Symfony\Config\Twig\DateConfig($config['date']);
            unset($config['date']);
        }

        if (array_key_exists('number_format', $config)) {
            $this->_usedProperties['numberFormat'] = true;
            $this->numberFormat = new \Symfony\Config\Twig\NumberFormatConfig($config['number_format']);
            unset($config['number_format']);
        }

        if (array_key_exists('mailer', $config)) {
            $this->_usedProperties['mailer'] = true;
            $this->mailer = new \Symfony\Config\Twig\MailerConfig($config['mailer']);
            unset($config['mailer']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['formThemes'])) {
            $output['form_themes'] = $this->formThemes;
        }
        if (isset($this->_usedProperties['globals'])) {
            $output['globals'] = array_map(fn ($v) => $v instanceof \Symfony\Config\Twig\GlobalConfig ? $v->toArray() : $v, $this->globals);
        }
        if (isset($this->_usedProperties['autoescapeService'])) {
            $output['autoescape_service'] = $this->autoescapeService;
        }
        if (isset($this->_usedProperties['autoescapeServiceMethod'])) {
            $output['autoescape_service_method'] = $this->autoescapeServiceMethod;
        }
        if (isset($this->_usedProperties['baseTemplateClass'])) {
            $output['base_template_class'] = $this->baseTemplateClass;
        }
        if (isset($this->_usedProperties['cache'])) {
            $output['cache'] = $this->cache;
        }
        if (isset($this->_usedProperties['charset'])) {
            $output['charset'] = $this->charset;
        }
        if (isset($this->_usedProperties['debug'])) {
            $output['debug'] = $this->debug;
        }
        if (isset($this->_usedProperties['strictVariables'])) {
            $output['strict_variables'] = $this->strictVariables;
        }
        if (isset($this->_usedProperties['autoReload'])) {
            $output['auto_reload'] = $this->autoReload;
        }
        if (isset($this->_usedProperties['optimizations'])) {
            $output['optimizations'] = $this->optimizations;
        }
        if (isset($this->_usedProperties['defaultPath'])) {
            $output['default_path'] = $this->defaultPath;
        }
        if (isset($this->_usedProperties['fileNamePattern'])) {
            $output['file_name_pattern'] = $this->fileNamePattern;
        }
        if (isset($this->_usedProperties['paths'])) {
            $output['paths'] = $this->paths;
        }
        if (isset($this->_usedProperties['date'])) {
            $output['date'] = $this->date->toArray();
        }
        if (isset($this->_usedProperties['numberFormat'])) {
            $output['number_format'] = $this->numberFormat->toArray();
        }
        if (isset($this->_usedProperties['mailer'])) {
            $output['mailer'] = $this->mailer->toArray();
        }
        if ($this->_hasDeprecatedCalls) {
            trigger_deprecation('symfony/config', '7.4', 'Calling any fluent method on "%s" is deprecated; pass the configuration to the constructor instead.', $this::class);
        }

        return $output;
    }

}
