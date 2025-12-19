<?php

namespace Symfony\Config;

require_once __DIR__.\DIRECTORY_SEPARATOR.'Framework'.\DIRECTORY_SEPARATOR.'CsrfProtectionConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Framework'.\DIRECTORY_SEPARATOR.'FormConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Framework'.\DIRECTORY_SEPARATOR.'HttpCacheConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Framework'.\DIRECTORY_SEPARATOR.'EsiConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Framework'.\DIRECTORY_SEPARATOR.'SsiConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Framework'.\DIRECTORY_SEPARATOR.'FragmentsConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Framework'.\DIRECTORY_SEPARATOR.'ProfilerConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Framework'.\DIRECTORY_SEPARATOR.'WorkflowsConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Framework'.\DIRECTORY_SEPARATOR.'RouterConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Framework'.\DIRECTORY_SEPARATOR.'SessionConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Framework'.\DIRECTORY_SEPARATOR.'RequestConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Framework'.\DIRECTORY_SEPARATOR.'AssetsConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Framework'.\DIRECTORY_SEPARATOR.'AssetMapperConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Framework'.\DIRECTORY_SEPARATOR.'TranslatorConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Framework'.\DIRECTORY_SEPARATOR.'ValidationConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Framework'.\DIRECTORY_SEPARATOR.'AnnotationsConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Framework'.\DIRECTORY_SEPARATOR.'SerializerConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Framework'.\DIRECTORY_SEPARATOR.'PropertyAccessConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Framework'.\DIRECTORY_SEPARATOR.'TypeInfoConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Framework'.\DIRECTORY_SEPARATOR.'PropertyInfoConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Framework'.\DIRECTORY_SEPARATOR.'CacheConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Framework'.\DIRECTORY_SEPARATOR.'PhpErrorsConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Framework'.\DIRECTORY_SEPARATOR.'ExceptionConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Framework'.\DIRECTORY_SEPARATOR.'WebLinkConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Framework'.\DIRECTORY_SEPARATOR.'LockConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Framework'.\DIRECTORY_SEPARATOR.'SemaphoreConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Framework'.\DIRECTORY_SEPARATOR.'MessengerConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Framework'.\DIRECTORY_SEPARATOR.'SchedulerConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Framework'.\DIRECTORY_SEPARATOR.'HttpClientConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Framework'.\DIRECTORY_SEPARATOR.'MailerConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Framework'.\DIRECTORY_SEPARATOR.'SecretsConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Framework'.\DIRECTORY_SEPARATOR.'NotifierConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Framework'.\DIRECTORY_SEPARATOR.'RateLimiterConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Framework'.\DIRECTORY_SEPARATOR.'UidConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Framework'.\DIRECTORY_SEPARATOR.'HtmlSanitizerConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Framework'.\DIRECTORY_SEPARATOR.'WebhookConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Framework'.\DIRECTORY_SEPARATOR.'RemoteeventConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Framework'.\DIRECTORY_SEPARATOR.'JsonStreamerConfig.php';

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class FrameworkConfig implements \Symfony\Component\Config\Builder\ConfigBuilderInterface
{
    private $secret;
    private $httpMethodOverride;
    private $trustXSendfileTypeHeader;
    private $ide;
    private $test;
    private $defaultLocale;
    private $setLocaleFromAcceptLanguage;
    private $setContentLanguageFromLocale;
    private $enabledLocales;
    private $trustedHosts;
    private $trustedProxies;
    private $trustedHeaders;
    private $errorController;
    private $handleAllThrowables;
    private $csrfProtection;
    private $form;
    private $httpCache;
    private $esi;
    private $ssi;
    private $fragments;
    private $profiler;
    private $workflows;
    private $router;
    private $session;
    private $request;
    private $assets;
    private $assetMapper;
    private $translator;
    private $validation;
    private $annotations;
    private $serializer;
    private $propertyAccess;
    private $typeInfo;
    private $propertyInfo;
    private $cache;
    private $phpErrors;
    private $exceptions;
    private $webLink;
    private $lock;
    private $semaphore;
    private $messenger;
    private $scheduler;
    private $disallowSearchEngineIndex;
    private $httpClient;
    private $mailer;
    private $secrets;
    private $notifier;
    private $rateLimiter;
    private $uid;
    private $htmlSanitizer;
    private $webhook;
    private $remoteevent;
    private $jsonStreamer;
    private $_usedProperties = [];
    private $_hasDeprecatedCalls = false;

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function secret($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['secret'] = true;
        $this->secret = $value;

        return $this;
    }

    /**
     * Set true to enable support for the '_method' request parameter to determine the intended HTTP method on POST requests. Note: When using the HttpCache, you need to call the method in your front controller instead.
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function httpMethodOverride($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['httpMethodOverride'] = true;
        $this->httpMethodOverride = $value;

        return $this;
    }

    /**
     * Set true to enable support for xsendfile in binary file responses.
     * @default '%env(bool:default::SYMFONY_TRUST_X_SENDFILE_TYPE_HEADER)%'
     * @param ParamConfigurator|mixed $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function trustXSendfileTypeHeader($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['trustXSendfileTypeHeader'] = true;
        $this->trustXSendfileTypeHeader = $value;

        return $this;
    }

    /**
     * @default '%env(default::SYMFONY_IDE)%'
     * @param ParamConfigurator|mixed $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function ide($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['ide'] = true;
        $this->ide = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|bool $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function test($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['test'] = true;
        $this->test = $value;

        return $this;
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
     * Whether to use the Accept-Language HTTP header to set the Request locale (only when the "_locale" request attribute is not passed).
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function setLocaleFromAcceptLanguage($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['setLocaleFromAcceptLanguage'] = true;
        $this->setLocaleFromAcceptLanguage = $value;

        return $this;
    }

    /**
     * Whether to set the Content-Language HTTP header on the Response using the Request locale.
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function setContentLanguageFromLocale($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['setContentLanguageFromLocale'] = true;
        $this->setContentLanguageFromLocale = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed> $value
     *
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function enabledLocales(ParamConfigurator|array $value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['enabledLocales'] = true;
        $this->enabledLocales = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed>|string $value
     *
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function trustedHosts(ParamConfigurator|string|array $value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['trustedHosts'] = true;
        $this->trustedHosts = $value;

        return $this;
    }

    /**
     * @default array (
     *   0 => '%env(default::SYMFONY_TRUSTED_PROXIES)%',
     * )
     * @param ParamConfigurator|mixed $value
     *
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function trustedProxies(mixed $value = array (
      0 => '%env(default::SYMFONY_TRUSTED_PROXIES)%',
    )): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['trustedProxies'] = true;
        $this->trustedProxies = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed>|string $value
     *
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function trustedHeaders(ParamConfigurator|string|array $value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['trustedHeaders'] = true;
        $this->trustedHeaders = $value;

        return $this;
    }

    /**
     * @default 'error_controller'
     * @param ParamConfigurator|mixed $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function errorController($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['errorController'] = true;
        $this->errorController = $value;

        return $this;
    }

    /**
     * HttpKernel will handle all kinds of \Throwable.
     * @default true
     * @param ParamConfigurator|bool $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function handleAllThrowables($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['handleAllThrowables'] = true;
        $this->handleAllThrowables = $value;

        return $this;
    }

    /**
     * @template TValue of bool|array
     * @param TValue $value
     * @default {"enabled":null,"stateless_token_ids":[],"check_header":false,"cookie_name":"csrf-token"}
     * @return \Symfony\Config\Framework\CsrfProtectionConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\Framework\CsrfProtectionConfig : static)
     * @deprecated since Symfony 7.4
     */
    public function csrfProtection(bool|array $value = []): \Symfony\Config\Framework\CsrfProtectionConfig|static
    {
        $this->_hasDeprecatedCalls = true;
        if (!\is_array($value)) {
            $this->_usedProperties['csrfProtection'] = true;
            $this->csrfProtection = $value;

            return $this;
        }

        if (!$this->csrfProtection instanceof \Symfony\Config\Framework\CsrfProtectionConfig) {
            $this->_usedProperties['csrfProtection'] = true;
            $this->csrfProtection = new \Symfony\Config\Framework\CsrfProtectionConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "csrfProtection()" has already been initialized. You cannot pass values the second time you call csrfProtection().');
        }

        return $this->csrfProtection;
    }

    /**
     * @template TValue of array|bool
     * @param TValue $value
     * Form configuration
     * @default {"enabled":false,"csrf_protection":{"enabled":null,"token_id":null,"field_name":"_token","field_attr":{"data-controller":"csrf-protection"}}}
     * @return \Symfony\Config\Framework\FormConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\Framework\FormConfig : static)
     * @deprecated since Symfony 7.4
     */
    public function form(array|bool $value = []): \Symfony\Config\Framework\FormConfig|static
    {
        $this->_hasDeprecatedCalls = true;
        if (!\is_array($value)) {
            $this->_usedProperties['form'] = true;
            $this->form = $value;

            return $this;
        }

        if (!$this->form instanceof \Symfony\Config\Framework\FormConfig) {
            $this->_usedProperties['form'] = true;
            $this->form = new \Symfony\Config\Framework\FormConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "form()" has already been initialized. You cannot pass values the second time you call form().');
        }

        return $this->form;
    }

    /**
     * @template TValue of array|bool
     * @param TValue $value
     * HTTP cache configuration
     * @default {"enabled":false,"debug":"%kernel.debug%","private_headers":[],"skip_response_headers":[]}
     * @return \Symfony\Config\Framework\HttpCacheConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\Framework\HttpCacheConfig : static)
     * @deprecated since Symfony 7.4
     */
    public function httpCache(array|bool $value = []): \Symfony\Config\Framework\HttpCacheConfig|static
    {
        $this->_hasDeprecatedCalls = true;
        if (!\is_array($value)) {
            $this->_usedProperties['httpCache'] = true;
            $this->httpCache = $value;

            return $this;
        }

        if (!$this->httpCache instanceof \Symfony\Config\Framework\HttpCacheConfig) {
            $this->_usedProperties['httpCache'] = true;
            $this->httpCache = new \Symfony\Config\Framework\HttpCacheConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "httpCache()" has already been initialized. You cannot pass values the second time you call httpCache().');
        }

        return $this->httpCache;
    }

    /**
     * @template TValue of array|bool
     * @param TValue $value
     * ESI configuration
     * @default {"enabled":false}
     * @return \Symfony\Config\Framework\EsiConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\Framework\EsiConfig : static)
     * @deprecated since Symfony 7.4
     */
    public function esi(array|bool $value = []): \Symfony\Config\Framework\EsiConfig|static
    {
        $this->_hasDeprecatedCalls = true;
        if (!\is_array($value)) {
            $this->_usedProperties['esi'] = true;
            $this->esi = $value;

            return $this;
        }

        if (!$this->esi instanceof \Symfony\Config\Framework\EsiConfig) {
            $this->_usedProperties['esi'] = true;
            $this->esi = new \Symfony\Config\Framework\EsiConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "esi()" has already been initialized. You cannot pass values the second time you call esi().');
        }

        return $this->esi;
    }

    /**
     * @template TValue of array|bool
     * @param TValue $value
     * SSI configuration
     * @default {"enabled":false}
     * @return \Symfony\Config\Framework\SsiConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\Framework\SsiConfig : static)
     * @deprecated since Symfony 7.4
     */
    public function ssi(array|bool $value = []): \Symfony\Config\Framework\SsiConfig|static
    {
        $this->_hasDeprecatedCalls = true;
        if (!\is_array($value)) {
            $this->_usedProperties['ssi'] = true;
            $this->ssi = $value;

            return $this;
        }

        if (!$this->ssi instanceof \Symfony\Config\Framework\SsiConfig) {
            $this->_usedProperties['ssi'] = true;
            $this->ssi = new \Symfony\Config\Framework\SsiConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "ssi()" has already been initialized. You cannot pass values the second time you call ssi().');
        }

        return $this->ssi;
    }

    /**
     * @template TValue of array|bool
     * @param TValue $value
     * Fragments configuration
     * @default {"enabled":false,"hinclude_default_template":null,"path":"\/_fragment"}
     * @return \Symfony\Config\Framework\FragmentsConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\Framework\FragmentsConfig : static)
     * @deprecated since Symfony 7.4
     */
    public function fragments(array|bool $value = []): \Symfony\Config\Framework\FragmentsConfig|static
    {
        $this->_hasDeprecatedCalls = true;
        if (!\is_array($value)) {
            $this->_usedProperties['fragments'] = true;
            $this->fragments = $value;

            return $this;
        }

        if (!$this->fragments instanceof \Symfony\Config\Framework\FragmentsConfig) {
            $this->_usedProperties['fragments'] = true;
            $this->fragments = new \Symfony\Config\Framework\FragmentsConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "fragments()" has already been initialized. You cannot pass values the second time you call fragments().');
        }

        return $this->fragments;
    }

    /**
     * @template TValue of array|bool
     * @param TValue $value
     * Profiler configuration
     * @default {"enabled":false,"collect":true,"collect_parameter":null,"only_exceptions":false,"only_main_requests":false,"dsn":"file:%kernel.cache_dir%\/profiler","collect_serializer_data":false}
     * @return \Symfony\Config\Framework\ProfilerConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\Framework\ProfilerConfig : static)
     * @deprecated since Symfony 7.4
     */
    public function profiler(array|bool $value = []): \Symfony\Config\Framework\ProfilerConfig|static
    {
        $this->_hasDeprecatedCalls = true;
        if (!\is_array($value)) {
            $this->_usedProperties['profiler'] = true;
            $this->profiler = $value;

            return $this;
        }

        if (!$this->profiler instanceof \Symfony\Config\Framework\ProfilerConfig) {
            $this->_usedProperties['profiler'] = true;
            $this->profiler = new \Symfony\Config\Framework\ProfilerConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "profiler()" has already been initialized. You cannot pass values the second time you call profiler().');
        }

        return $this->profiler;
    }

    /**
     * @template TValue of mixed
     * @param TValue $value
     * @default {"enabled":false,"workflows":[]}
     * @return \Symfony\Config\Framework\WorkflowsConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\Framework\WorkflowsConfig : static)
     * @deprecated since Symfony 7.4
     */
    public function workflows(mixed $value = []): \Symfony\Config\Framework\WorkflowsConfig|static
    {
        $this->_hasDeprecatedCalls = true;
        if (!\is_array($value)) {
            $this->_usedProperties['workflows'] = true;
            $this->workflows = $value;

            return $this;
        }

        if (!$this->workflows instanceof \Symfony\Config\Framework\WorkflowsConfig) {
            $this->_usedProperties['workflows'] = true;
            $this->workflows = new \Symfony\Config\Framework\WorkflowsConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "workflows()" has already been initialized. You cannot pass values the second time you call workflows().');
        }

        return $this->workflows;
    }

    /**
     * @template TValue of array|bool
     * @param TValue $value
     * Router configuration
     * @default {"enabled":false,"cache_dir":"%kernel.build_dir%","default_uri":null,"http_port":80,"https_port":443,"strict_requirements":true,"utf8":true}
     * @return \Symfony\Config\Framework\RouterConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\Framework\RouterConfig : static)
     * @deprecated since Symfony 7.4
     */
    public function router(array|bool $value = []): \Symfony\Config\Framework\RouterConfig|static
    {
        $this->_hasDeprecatedCalls = true;
        if (!\is_array($value)) {
            $this->_usedProperties['router'] = true;
            $this->router = $value;

            return $this;
        }

        if (!$this->router instanceof \Symfony\Config\Framework\RouterConfig) {
            $this->_usedProperties['router'] = true;
            $this->router = new \Symfony\Config\Framework\RouterConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "router()" has already been initialized. You cannot pass values the second time you call router().');
        }

        return $this->router;
    }

    /**
     * @template TValue of array|bool
     * @param TValue $value
     * Session configuration
     * @default {"enabled":false,"storage_factory_id":"session.storage.factory.native","cookie_secure":"auto","cookie_httponly":true,"cookie_samesite":"lax","metadata_update_threshold":0}
     * @return \Symfony\Config\Framework\SessionConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\Framework\SessionConfig : static)
     * @deprecated since Symfony 7.4
     */
    public function session(array|bool $value = []): \Symfony\Config\Framework\SessionConfig|static
    {
        $this->_hasDeprecatedCalls = true;
        if (!\is_array($value)) {
            $this->_usedProperties['session'] = true;
            $this->session = $value;

            return $this;
        }

        if (!$this->session instanceof \Symfony\Config\Framework\SessionConfig) {
            $this->_usedProperties['session'] = true;
            $this->session = new \Symfony\Config\Framework\SessionConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "session()" has already been initialized. You cannot pass values the second time you call session().');
        }

        return $this->session;
    }

    /**
     * @template TValue of array|bool
     * @param TValue $value
     * Request configuration
     * @default {"enabled":false,"formats":[]}
     * @return \Symfony\Config\Framework\RequestConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\Framework\RequestConfig : static)
     * @deprecated since Symfony 7.4
     */
    public function request(array|bool $value = []): \Symfony\Config\Framework\RequestConfig|static
    {
        $this->_hasDeprecatedCalls = true;
        if (!\is_array($value)) {
            $this->_usedProperties['request'] = true;
            $this->request = $value;

            return $this;
        }

        if (!$this->request instanceof \Symfony\Config\Framework\RequestConfig) {
            $this->_usedProperties['request'] = true;
            $this->request = new \Symfony\Config\Framework\RequestConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "request()" has already been initialized. You cannot pass values the second time you call request().');
        }

        return $this->request;
    }

    /**
     * @template TValue of array|bool
     * @param TValue $value
     * Assets configuration
     * @default {"enabled":true,"strict_mode":false,"version_strategy":null,"version":null,"version_format":"%%s?%%s","json_manifest_path":null,"base_path":"","base_urls":[],"packages":[]}
     * @return \Symfony\Config\Framework\AssetsConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\Framework\AssetsConfig : static)
     * @deprecated since Symfony 7.4
     */
    public function assets(array|bool $value = []): \Symfony\Config\Framework\AssetsConfig|static
    {
        $this->_hasDeprecatedCalls = true;
        if (!\is_array($value)) {
            $this->_usedProperties['assets'] = true;
            $this->assets = $value;

            return $this;
        }

        if (!$this->assets instanceof \Symfony\Config\Framework\AssetsConfig) {
            $this->_usedProperties['assets'] = true;
            $this->assets = new \Symfony\Config\Framework\AssetsConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "assets()" has already been initialized. You cannot pass values the second time you call assets().');
        }

        return $this->assets;
    }

    /**
     * @template TValue of array|bool
     * @param TValue $value
     * Asset Mapper configuration
     * @default {"enabled":false,"paths":[],"excluded_patterns":[],"exclude_dotfiles":true,"server":true,"public_prefix":"\/assets\/","missing_import_mode":"warn","extensions":[],"importmap_path":"%kernel.project_dir%\/importmap.php","importmap_polyfill":"es-module-shims","importmap_script_attributes":[],"vendor_dir":"%kernel.project_dir%\/assets\/vendor","precompress":{"enabled":false,"formats":[],"extensions":[]}}
     * @return \Symfony\Config\Framework\AssetMapperConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\Framework\AssetMapperConfig : static)
     * @deprecated since Symfony 7.4
     */
    public function assetMapper(array|bool $value = []): \Symfony\Config\Framework\AssetMapperConfig|static
    {
        $this->_hasDeprecatedCalls = true;
        if (!\is_array($value)) {
            $this->_usedProperties['assetMapper'] = true;
            $this->assetMapper = $value;

            return $this;
        }

        if (!$this->assetMapper instanceof \Symfony\Config\Framework\AssetMapperConfig) {
            $this->_usedProperties['assetMapper'] = true;
            $this->assetMapper = new \Symfony\Config\Framework\AssetMapperConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "assetMapper()" has already been initialized. You cannot pass values the second time you call assetMapper().');
        }

        return $this->assetMapper;
    }

    /**
     * @template TValue of array|bool
     * @param TValue $value
     * Translator configuration
     * @default {"enabled":true,"fallbacks":[],"logging":false,"formatter":"translator.formatter.default","cache_dir":"%kernel.cache_dir%\/translations","default_path":"%kernel.project_dir%\/translations","paths":[],"pseudo_localization":{"enabled":false,"accents":true,"expansion_factor":1,"brackets":true,"parse_html":false,"localizable_html_attributes":[]},"providers":[],"globals":[]}
     * @return \Symfony\Config\Framework\TranslatorConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\Framework\TranslatorConfig : static)
     * @deprecated since Symfony 7.4
     */
    public function translator(array|bool $value = []): \Symfony\Config\Framework\TranslatorConfig|static
    {
        $this->_hasDeprecatedCalls = true;
        if (!\is_array($value)) {
            $this->_usedProperties['translator'] = true;
            $this->translator = $value;

            return $this;
        }

        if (!$this->translator instanceof \Symfony\Config\Framework\TranslatorConfig) {
            $this->_usedProperties['translator'] = true;
            $this->translator = new \Symfony\Config\Framework\TranslatorConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "translator()" has already been initialized. You cannot pass values the second time you call translator().');
        }

        return $this->translator;
    }

    /**
     * @template TValue of array|bool
     * @param TValue $value
     * Validation configuration
     * @default {"enabled":true,"enable_attributes":true,"static_method":["loadValidatorMetadata"],"translation_domain":"validators","email_validation_mode":"html5","mapping":{"paths":[]},"not_compromised_password":{"enabled":true,"endpoint":null},"disable_translation":false,"auto_mapping":[]}
     * @return \Symfony\Config\Framework\ValidationConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\Framework\ValidationConfig : static)
     * @deprecated since Symfony 7.4
     */
    public function validation(array|bool $value = []): \Symfony\Config\Framework\ValidationConfig|static
    {
        $this->_hasDeprecatedCalls = true;
        if (!\is_array($value)) {
            $this->_usedProperties['validation'] = true;
            $this->validation = $value;

            return $this;
        }

        if (!$this->validation instanceof \Symfony\Config\Framework\ValidationConfig) {
            $this->_usedProperties['validation'] = true;
            $this->validation = new \Symfony\Config\Framework\ValidationConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "validation()" has already been initialized. You cannot pass values the second time you call validation().');
        }

        return $this->validation;
    }

    /**
     * @template TValue of array|bool
     * @param TValue $value
     * @default {"enabled":false}
     * @return \Symfony\Config\Framework\AnnotationsConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\Framework\AnnotationsConfig : static)
     * @deprecated since Symfony 7.4
     */
    public function annotations(array|bool $value = []): \Symfony\Config\Framework\AnnotationsConfig|static
    {
        $this->_hasDeprecatedCalls = true;
        if (!\is_array($value)) {
            $this->_usedProperties['annotations'] = true;
            $this->annotations = $value;

            return $this;
        }

        if (!$this->annotations instanceof \Symfony\Config\Framework\AnnotationsConfig) {
            $this->_usedProperties['annotations'] = true;
            $this->annotations = new \Symfony\Config\Framework\AnnotationsConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "annotations()" has already been initialized. You cannot pass values the second time you call annotations().');
        }

        return $this->annotations;
    }

    /**
     * @template TValue of array|bool
     * @param TValue $value
     * Serializer configuration
     * @default {"enabled":true,"enable_attributes":true,"mapping":{"paths":[]},"default_context":[],"named_serializers":[]}
     * @return \Symfony\Config\Framework\SerializerConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\Framework\SerializerConfig : static)
     * @deprecated since Symfony 7.4
     */
    public function serializer(array|bool $value = []): \Symfony\Config\Framework\SerializerConfig|static
    {
        $this->_hasDeprecatedCalls = true;
        if (!\is_array($value)) {
            $this->_usedProperties['serializer'] = true;
            $this->serializer = $value;

            return $this;
        }

        if (!$this->serializer instanceof \Symfony\Config\Framework\SerializerConfig) {
            $this->_usedProperties['serializer'] = true;
            $this->serializer = new \Symfony\Config\Framework\SerializerConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "serializer()" has already been initialized. You cannot pass values the second time you call serializer().');
        }

        return $this->serializer;
    }

    /**
     * @template TValue of array|bool
     * @param TValue $value
     * Property access configuration
     * @default {"enabled":true,"magic_call":false,"magic_get":true,"magic_set":true,"throw_exception_on_invalid_index":false,"throw_exception_on_invalid_property_path":true}
     * @return \Symfony\Config\Framework\PropertyAccessConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\Framework\PropertyAccessConfig : static)
     * @deprecated since Symfony 7.4
     */
    public function propertyAccess(array|bool $value = []): \Symfony\Config\Framework\PropertyAccessConfig|static
    {
        $this->_hasDeprecatedCalls = true;
        if (!\is_array($value)) {
            $this->_usedProperties['propertyAccess'] = true;
            $this->propertyAccess = $value;

            return $this;
        }

        if (!$this->propertyAccess instanceof \Symfony\Config\Framework\PropertyAccessConfig) {
            $this->_usedProperties['propertyAccess'] = true;
            $this->propertyAccess = new \Symfony\Config\Framework\PropertyAccessConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "propertyAccess()" has already been initialized. You cannot pass values the second time you call propertyAccess().');
        }

        return $this->propertyAccess;
    }

    /**
     * @template TValue of array|bool
     * @param TValue $value
     * Type info configuration
     * @default {"enabled":true}
     * @return \Symfony\Config\Framework\TypeInfoConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\Framework\TypeInfoConfig : static)
     * @deprecated since Symfony 7.4
     */
    public function typeInfo(array|bool $value = []): \Symfony\Config\Framework\TypeInfoConfig|static
    {
        $this->_hasDeprecatedCalls = true;
        if (!\is_array($value)) {
            $this->_usedProperties['typeInfo'] = true;
            $this->typeInfo = $value;

            return $this;
        }

        if (!$this->typeInfo instanceof \Symfony\Config\Framework\TypeInfoConfig) {
            $this->_usedProperties['typeInfo'] = true;
            $this->typeInfo = new \Symfony\Config\Framework\TypeInfoConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "typeInfo()" has already been initialized. You cannot pass values the second time you call typeInfo().');
        }

        return $this->typeInfo;
    }

    /**
     * @template TValue of array|bool
     * @param TValue $value
     * Property info configuration
     * @default {"enabled":true}
     * @return \Symfony\Config\Framework\PropertyInfoConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\Framework\PropertyInfoConfig : static)
     * @deprecated since Symfony 7.4
     */
    public function propertyInfo(array|bool $value = []): \Symfony\Config\Framework\PropertyInfoConfig|static
    {
        $this->_hasDeprecatedCalls = true;
        if (!\is_array($value)) {
            $this->_usedProperties['propertyInfo'] = true;
            $this->propertyInfo = $value;

            return $this;
        }

        if (!$this->propertyInfo instanceof \Symfony\Config\Framework\PropertyInfoConfig) {
            $this->_usedProperties['propertyInfo'] = true;
            $this->propertyInfo = new \Symfony\Config\Framework\PropertyInfoConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "propertyInfo()" has already been initialized. You cannot pass values the second time you call propertyInfo().');
        }

        return $this->propertyInfo;
    }

    /**
     * Cache configuration
     * @default {"prefix_seed":"_%kernel.project_dir%.%kernel.container_class%","app":"cache.adapter.filesystem","system":"cache.adapter.system","directory":"%kernel.cache_dir%\/pools\/app","default_redis_provider":"redis:\/\/localhost","default_valkey_provider":"valkey:\/\/localhost","default_memcached_provider":"memcached:\/\/localhost","default_doctrine_dbal_provider":"database_connection","default_pdo_provider":null,"pools":[]}
     * @deprecated since Symfony 7.4
     */
    public function cache(array $value = []): \Symfony\Config\Framework\CacheConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (null === $this->cache) {
            $this->_usedProperties['cache'] = true;
            $this->cache = new \Symfony\Config\Framework\CacheConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "cache()" has already been initialized. You cannot pass values the second time you call cache().');
        }

        return $this->cache;
    }

    /**
     * PHP errors handling configuration
     * @default {"log":true,"throw":true}
     * @deprecated since Symfony 7.4
     */
    public function phpErrors(array $value = []): \Symfony\Config\Framework\PhpErrorsConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (null === $this->phpErrors) {
            $this->_usedProperties['phpErrors'] = true;
            $this->phpErrors = new \Symfony\Config\Framework\PhpErrorsConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "phpErrors()" has already been initialized. You cannot pass values the second time you call phpErrors().');
        }

        return $this->phpErrors;
    }

    /**
     * Exception handling configuration
     * @deprecated since Symfony 7.4
     */
    public function exception(string $class, array $value = []): \Symfony\Config\Framework\ExceptionConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (!isset($this->exceptions[$class])) {
            $this->_usedProperties['exceptions'] = true;
            $this->exceptions[$class] = new \Symfony\Config\Framework\ExceptionConfig($value);
        } elseif (1 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "exception()" has already been initialized. You cannot pass values the second time you call exception().');
        }

        return $this->exceptions[$class];
    }

    /**
     * @template TValue of array|bool
     * @param TValue $value
     * Web links configuration
     * @default {"enabled":false}
     * @return \Symfony\Config\Framework\WebLinkConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\Framework\WebLinkConfig : static)
     * @deprecated since Symfony 7.4
     */
    public function webLink(array|bool $value = []): \Symfony\Config\Framework\WebLinkConfig|static
    {
        $this->_hasDeprecatedCalls = true;
        if (!\is_array($value)) {
            $this->_usedProperties['webLink'] = true;
            $this->webLink = $value;

            return $this;
        }

        if (!$this->webLink instanceof \Symfony\Config\Framework\WebLinkConfig) {
            $this->_usedProperties['webLink'] = true;
            $this->webLink = new \Symfony\Config\Framework\WebLinkConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "webLink()" has already been initialized. You cannot pass values the second time you call webLink().');
        }

        return $this->webLink;
    }

    /**
     * @template TValue of mixed
     * @param TValue $value
     * Lock configuration
     * @default {"enabled":true,"resources":{"default":["flock"]}}
     * @return \Symfony\Config\Framework\LockConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\Framework\LockConfig : static)
     * @deprecated since Symfony 7.4
     */
    public function lock(mixed $value = []): \Symfony\Config\Framework\LockConfig|static
    {
        $this->_hasDeprecatedCalls = true;
        if (!\is_array($value)) {
            $this->_usedProperties['lock'] = true;
            $this->lock = $value;

            return $this;
        }

        if (!$this->lock instanceof \Symfony\Config\Framework\LockConfig) {
            $this->_usedProperties['lock'] = true;
            $this->lock = new \Symfony\Config\Framework\LockConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "lock()" has already been initialized. You cannot pass values the second time you call lock().');
        }

        return $this->lock;
    }

    /**
     * @template TValue of mixed
     * @param TValue $value
     * Semaphore configuration
     * @default {"enabled":false,"resources":[]}
     * @return \Symfony\Config\Framework\SemaphoreConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\Framework\SemaphoreConfig : static)
     * @deprecated since Symfony 7.4
     */
    public function semaphore(mixed $value = []): \Symfony\Config\Framework\SemaphoreConfig|static
    {
        $this->_hasDeprecatedCalls = true;
        if (!\is_array($value)) {
            $this->_usedProperties['semaphore'] = true;
            $this->semaphore = $value;

            return $this;
        }

        if (!$this->semaphore instanceof \Symfony\Config\Framework\SemaphoreConfig) {
            $this->_usedProperties['semaphore'] = true;
            $this->semaphore = new \Symfony\Config\Framework\SemaphoreConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "semaphore()" has already been initialized. You cannot pass values the second time you call semaphore().');
        }

        return $this->semaphore;
    }

    /**
     * @template TValue of array|bool
     * @param TValue $value
     * Messenger configuration
     * @default {"enabled":true,"routing":[],"serializer":{"default_serializer":"messenger.transport.native_php_serializer","symfony_serializer":{"format":"json","context":[]}},"transports":[],"failure_transport":null,"stop_worker_on_signals":[],"default_bus":null,"buses":{"messenger.bus.default":{"default_middleware":{"enabled":true,"allow_no_handlers":false,"allow_no_senders":true},"middleware":[]}}}
     * @return \Symfony\Config\Framework\MessengerConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\Framework\MessengerConfig : static)
     * @deprecated since Symfony 7.4
     */
    public function messenger(array|bool $value = []): \Symfony\Config\Framework\MessengerConfig|static
    {
        $this->_hasDeprecatedCalls = true;
        if (!\is_array($value)) {
            $this->_usedProperties['messenger'] = true;
            $this->messenger = $value;

            return $this;
        }

        if (!$this->messenger instanceof \Symfony\Config\Framework\MessengerConfig) {
            $this->_usedProperties['messenger'] = true;
            $this->messenger = new \Symfony\Config\Framework\MessengerConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "messenger()" has already been initialized. You cannot pass values the second time you call messenger().');
        }

        return $this->messenger;
    }

    /**
     * @template TValue of array|bool
     * @param TValue $value
     * Scheduler configuration
     * @default {"enabled":false}
     * @return \Symfony\Config\Framework\SchedulerConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\Framework\SchedulerConfig : static)
     * @deprecated since Symfony 7.4
     */
    public function scheduler(array|bool $value = []): \Symfony\Config\Framework\SchedulerConfig|static
    {
        $this->_hasDeprecatedCalls = true;
        if (!\is_array($value)) {
            $this->_usedProperties['scheduler'] = true;
            $this->scheduler = $value;

            return $this;
        }

        if (!$this->scheduler instanceof \Symfony\Config\Framework\SchedulerConfig) {
            $this->_usedProperties['scheduler'] = true;
            $this->scheduler = new \Symfony\Config\Framework\SchedulerConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "scheduler()" has already been initialized. You cannot pass values the second time you call scheduler().');
        }

        return $this->scheduler;
    }

    /**
     * Enabled by default when debug is enabled.
     * @default true
     * @param ParamConfigurator|bool $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function disallowSearchEngineIndex($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['disallowSearchEngineIndex'] = true;
        $this->disallowSearchEngineIndex = $value;

        return $this;
    }

    /**
     * @template TValue of mixed
     * @param TValue $value
     * HTTP Client configuration
     * @default {"enabled":true,"scoped_clients":[]}
     * @return \Symfony\Config\Framework\HttpClientConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\Framework\HttpClientConfig : static)
     * @deprecated since Symfony 7.4
     */
    public function httpClient(mixed $value = []): \Symfony\Config\Framework\HttpClientConfig|static
    {
        $this->_hasDeprecatedCalls = true;
        if (!\is_array($value)) {
            $this->_usedProperties['httpClient'] = true;
            $this->httpClient = $value;

            return $this;
        }

        if (!$this->httpClient instanceof \Symfony\Config\Framework\HttpClientConfig) {
            $this->_usedProperties['httpClient'] = true;
            $this->httpClient = new \Symfony\Config\Framework\HttpClientConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "httpClient()" has already been initialized. You cannot pass values the second time you call httpClient().');
        }

        return $this->httpClient;
    }

    /**
     * @template TValue of array|bool
     * @param TValue $value
     * Mailer configuration
     * @default {"enabled":true,"message_bus":null,"dsn":null,"transports":[],"headers":[],"dkim_signer":{"enabled":false,"key":"","domain":"","select":"","passphrase":"","options":[]},"smime_signer":{"enabled":false,"key":"","certificate":"","passphrase":null,"extra_certificates":null,"sign_options":null},"smime_encrypter":{"enabled":false,"repository":"","cipher":null}}
     * @return \Symfony\Config\Framework\MailerConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\Framework\MailerConfig : static)
     * @deprecated since Symfony 7.4
     */
    public function mailer(array|bool $value = []): \Symfony\Config\Framework\MailerConfig|static
    {
        $this->_hasDeprecatedCalls = true;
        if (!\is_array($value)) {
            $this->_usedProperties['mailer'] = true;
            $this->mailer = $value;

            return $this;
        }

        if (!$this->mailer instanceof \Symfony\Config\Framework\MailerConfig) {
            $this->_usedProperties['mailer'] = true;
            $this->mailer = new \Symfony\Config\Framework\MailerConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "mailer()" has already been initialized. You cannot pass values the second time you call mailer().');
        }

        return $this->mailer;
    }

    /**
     * @template TValue of array|bool
     * @param TValue $value
     * @default {"enabled":true,"vault_directory":"%kernel.project_dir%\/config\/secrets\/%kernel.runtime_environment%","local_dotenv_file":"%kernel.project_dir%\/.env.%kernel.runtime_environment%.local","decryption_env_var":"base64:default::SYMFONY_DECRYPTION_SECRET"}
     * @return \Symfony\Config\Framework\SecretsConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\Framework\SecretsConfig : static)
     * @deprecated since Symfony 7.4
     */
    public function secrets(array|bool $value = []): \Symfony\Config\Framework\SecretsConfig|static
    {
        $this->_hasDeprecatedCalls = true;
        if (!\is_array($value)) {
            $this->_usedProperties['secrets'] = true;
            $this->secrets = $value;

            return $this;
        }

        if (!$this->secrets instanceof \Symfony\Config\Framework\SecretsConfig) {
            $this->_usedProperties['secrets'] = true;
            $this->secrets = new \Symfony\Config\Framework\SecretsConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "secrets()" has already been initialized. You cannot pass values the second time you call secrets().');
        }

        return $this->secrets;
    }

    /**
     * @template TValue of array|bool
     * @param TValue $value
     * Notifier configuration
     * @default {"enabled":false,"message_bus":null,"chatter_transports":[],"texter_transports":[],"notification_on_failed_messages":false,"channel_policy":[],"admin_recipients":[]}
     * @return \Symfony\Config\Framework\NotifierConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\Framework\NotifierConfig : static)
     * @deprecated since Symfony 7.4
     */
    public function notifier(array|bool $value = []): \Symfony\Config\Framework\NotifierConfig|static
    {
        $this->_hasDeprecatedCalls = true;
        if (!\is_array($value)) {
            $this->_usedProperties['notifier'] = true;
            $this->notifier = $value;

            return $this;
        }

        if (!$this->notifier instanceof \Symfony\Config\Framework\NotifierConfig) {
            $this->_usedProperties['notifier'] = true;
            $this->notifier = new \Symfony\Config\Framework\NotifierConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "notifier()" has already been initialized. You cannot pass values the second time you call notifier().');
        }

        return $this->notifier;
    }

    /**
     * @template TValue of mixed
     * @param TValue $value
     * Rate limiter configuration
     * @default {"enabled":true,"limiters":[]}
     * @return \Symfony\Config\Framework\RateLimiterConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\Framework\RateLimiterConfig : static)
     * @deprecated since Symfony 7.4
     */
    public function rateLimiter(mixed $value = []): \Symfony\Config\Framework\RateLimiterConfig|static
    {
        $this->_hasDeprecatedCalls = true;
        if (!\is_array($value)) {
            $this->_usedProperties['rateLimiter'] = true;
            $this->rateLimiter = $value;

            return $this;
        }

        if (!$this->rateLimiter instanceof \Symfony\Config\Framework\RateLimiterConfig) {
            $this->_usedProperties['rateLimiter'] = true;
            $this->rateLimiter = new \Symfony\Config\Framework\RateLimiterConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "rateLimiter()" has already been initialized. You cannot pass values the second time you call rateLimiter().');
        }

        return $this->rateLimiter;
    }

    /**
     * @template TValue of array|bool
     * @param TValue $value
     * Uid configuration
     * @default {"enabled":true,"default_uuid_version":7,"name_based_uuid_version":5,"time_based_uuid_version":7}
     * @return \Symfony\Config\Framework\UidConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\Framework\UidConfig : static)
     * @deprecated since Symfony 7.4
     */
    public function uid(array|bool $value = []): \Symfony\Config\Framework\UidConfig|static
    {
        $this->_hasDeprecatedCalls = true;
        if (!\is_array($value)) {
            $this->_usedProperties['uid'] = true;
            $this->uid = $value;

            return $this;
        }

        if (!$this->uid instanceof \Symfony\Config\Framework\UidConfig) {
            $this->_usedProperties['uid'] = true;
            $this->uid = new \Symfony\Config\Framework\UidConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "uid()" has already been initialized. You cannot pass values the second time you call uid().');
        }

        return $this->uid;
    }

    /**
     * @template TValue of array|bool
     * @param TValue $value
     * HtmlSanitizer configuration
     * @default {"enabled":true,"sanitizers":[]}
     * @return \Symfony\Config\Framework\HtmlSanitizerConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\Framework\HtmlSanitizerConfig : static)
     * @deprecated since Symfony 7.4
     */
    public function htmlSanitizer(array|bool $value = []): \Symfony\Config\Framework\HtmlSanitizerConfig|static
    {
        $this->_hasDeprecatedCalls = true;
        if (!\is_array($value)) {
            $this->_usedProperties['htmlSanitizer'] = true;
            $this->htmlSanitizer = $value;

            return $this;
        }

        if (!$this->htmlSanitizer instanceof \Symfony\Config\Framework\HtmlSanitizerConfig) {
            $this->_usedProperties['htmlSanitizer'] = true;
            $this->htmlSanitizer = new \Symfony\Config\Framework\HtmlSanitizerConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "htmlSanitizer()" has already been initialized. You cannot pass values the second time you call htmlSanitizer().');
        }

        return $this->htmlSanitizer;
    }

    /**
     * @template TValue of array|bool
     * @param TValue $value
     * Webhook configuration
     * @default {"enabled":false,"message_bus":"messenger.default_bus","routing":[]}
     * @return \Symfony\Config\Framework\WebhookConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\Framework\WebhookConfig : static)
     * @deprecated since Symfony 7.4
     */
    public function webhook(array|bool $value = []): \Symfony\Config\Framework\WebhookConfig|static
    {
        $this->_hasDeprecatedCalls = true;
        if (!\is_array($value)) {
            $this->_usedProperties['webhook'] = true;
            $this->webhook = $value;

            return $this;
        }

        if (!$this->webhook instanceof \Symfony\Config\Framework\WebhookConfig) {
            $this->_usedProperties['webhook'] = true;
            $this->webhook = new \Symfony\Config\Framework\WebhookConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "webhook()" has already been initialized. You cannot pass values the second time you call webhook().');
        }

        return $this->webhook;
    }

    /**
     * @template TValue of array|bool
     * @param TValue $value
     * RemoteEvent configuration
     * @default {"enabled":false}
     * @return \Symfony\Config\Framework\RemoteeventConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\Framework\RemoteeventConfig : static)
     * @deprecated since Symfony 7.4
     */
    public function remoteevent(array|bool $value = []): \Symfony\Config\Framework\RemoteeventConfig|static
    {
        $this->_hasDeprecatedCalls = true;
        if (!\is_array($value)) {
            $this->_usedProperties['remoteevent'] = true;
            $this->remoteevent = $value;

            return $this;
        }

        if (!$this->remoteevent instanceof \Symfony\Config\Framework\RemoteeventConfig) {
            $this->_usedProperties['remoteevent'] = true;
            $this->remoteevent = new \Symfony\Config\Framework\RemoteeventConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "remoteevent()" has already been initialized. You cannot pass values the second time you call remoteevent().');
        }

        return $this->remoteevent;
    }

    /**
     * @template TValue of array|bool
     * @param TValue $value
     * JSON streamer configuration
     * @default {"enabled":false}
     * @return \Symfony\Config\Framework\JsonStreamerConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\Framework\JsonStreamerConfig : static)
     * @deprecated since Symfony 7.4
     */
    public function jsonStreamer(array|bool $value = []): \Symfony\Config\Framework\JsonStreamerConfig|static
    {
        $this->_hasDeprecatedCalls = true;
        if (!\is_array($value)) {
            $this->_usedProperties['jsonStreamer'] = true;
            $this->jsonStreamer = $value;

            return $this;
        }

        if (!$this->jsonStreamer instanceof \Symfony\Config\Framework\JsonStreamerConfig) {
            $this->_usedProperties['jsonStreamer'] = true;
            $this->jsonStreamer = new \Symfony\Config\Framework\JsonStreamerConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "jsonStreamer()" has already been initialized. You cannot pass values the second time you call jsonStreamer().');
        }

        return $this->jsonStreamer;
    }

    public function getExtensionAlias(): string
    {
        return 'framework';
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('secret', $config)) {
            $this->_usedProperties['secret'] = true;
            $this->secret = $config['secret'];
            unset($config['secret']);
        }

        if (array_key_exists('http_method_override', $config)) {
            $this->_usedProperties['httpMethodOverride'] = true;
            $this->httpMethodOverride = $config['http_method_override'];
            unset($config['http_method_override']);
        }

        if (array_key_exists('trust_x_sendfile_type_header', $config)) {
            $this->_usedProperties['trustXSendfileTypeHeader'] = true;
            $this->trustXSendfileTypeHeader = $config['trust_x_sendfile_type_header'];
            unset($config['trust_x_sendfile_type_header']);
        }

        if (array_key_exists('ide', $config)) {
            $this->_usedProperties['ide'] = true;
            $this->ide = $config['ide'];
            unset($config['ide']);
        }

        if (array_key_exists('test', $config)) {
            $this->_usedProperties['test'] = true;
            $this->test = $config['test'];
            unset($config['test']);
        }

        if (array_key_exists('default_locale', $config)) {
            $this->_usedProperties['defaultLocale'] = true;
            $this->defaultLocale = $config['default_locale'];
            unset($config['default_locale']);
        }

        if (array_key_exists('set_locale_from_accept_language', $config)) {
            $this->_usedProperties['setLocaleFromAcceptLanguage'] = true;
            $this->setLocaleFromAcceptLanguage = $config['set_locale_from_accept_language'];
            unset($config['set_locale_from_accept_language']);
        }

        if (array_key_exists('set_content_language_from_locale', $config)) {
            $this->_usedProperties['setContentLanguageFromLocale'] = true;
            $this->setContentLanguageFromLocale = $config['set_content_language_from_locale'];
            unset($config['set_content_language_from_locale']);
        }

        if (array_key_exists('enabled_locales', $config)) {
            $this->_usedProperties['enabledLocales'] = true;
            $this->enabledLocales = $config['enabled_locales'];
            unset($config['enabled_locales']);
        }

        if (array_key_exists('trusted_hosts', $config)) {
            $this->_usedProperties['trustedHosts'] = true;
            $this->trustedHosts = $config['trusted_hosts'];
            unset($config['trusted_hosts']);
        }

        if (array_key_exists('trusted_proxies', $config)) {
            $this->_usedProperties['trustedProxies'] = true;
            $this->trustedProxies = $config['trusted_proxies'];
            unset($config['trusted_proxies']);
        }

        if (array_key_exists('trusted_headers', $config)) {
            $this->_usedProperties['trustedHeaders'] = true;
            $this->trustedHeaders = $config['trusted_headers'];
            unset($config['trusted_headers']);
        }

        if (array_key_exists('error_controller', $config)) {
            $this->_usedProperties['errorController'] = true;
            $this->errorController = $config['error_controller'];
            unset($config['error_controller']);
        }

        if (array_key_exists('handle_all_throwables', $config)) {
            $this->_usedProperties['handleAllThrowables'] = true;
            $this->handleAllThrowables = $config['handle_all_throwables'];
            unset($config['handle_all_throwables']);
        }

        if (array_key_exists('csrf_protection', $config)) {
            $this->_usedProperties['csrfProtection'] = true;
            $this->csrfProtection = \is_array($config['csrf_protection']) ? new \Symfony\Config\Framework\CsrfProtectionConfig($config['csrf_protection']) : $config['csrf_protection'];
            unset($config['csrf_protection']);
        }

        if (array_key_exists('form', $config)) {
            $this->_usedProperties['form'] = true;
            $this->form = \is_array($config['form']) ? new \Symfony\Config\Framework\FormConfig($config['form']) : $config['form'];
            unset($config['form']);
        }

        if (array_key_exists('http_cache', $config)) {
            $this->_usedProperties['httpCache'] = true;
            $this->httpCache = \is_array($config['http_cache']) ? new \Symfony\Config\Framework\HttpCacheConfig($config['http_cache']) : $config['http_cache'];
            unset($config['http_cache']);
        }

        if (array_key_exists('esi', $config)) {
            $this->_usedProperties['esi'] = true;
            $this->esi = \is_array($config['esi']) ? new \Symfony\Config\Framework\EsiConfig($config['esi']) : $config['esi'];
            unset($config['esi']);
        }

        if (array_key_exists('ssi', $config)) {
            $this->_usedProperties['ssi'] = true;
            $this->ssi = \is_array($config['ssi']) ? new \Symfony\Config\Framework\SsiConfig($config['ssi']) : $config['ssi'];
            unset($config['ssi']);
        }

        if (array_key_exists('fragments', $config)) {
            $this->_usedProperties['fragments'] = true;
            $this->fragments = \is_array($config['fragments']) ? new \Symfony\Config\Framework\FragmentsConfig($config['fragments']) : $config['fragments'];
            unset($config['fragments']);
        }

        if (array_key_exists('profiler', $config)) {
            $this->_usedProperties['profiler'] = true;
            $this->profiler = \is_array($config['profiler']) ? new \Symfony\Config\Framework\ProfilerConfig($config['profiler']) : $config['profiler'];
            unset($config['profiler']);
        }

        if (array_key_exists('workflows', $config)) {
            $this->_usedProperties['workflows'] = true;
            $this->workflows = \is_array($config['workflows']) ? new \Symfony\Config\Framework\WorkflowsConfig($config['workflows']) : $config['workflows'];
            unset($config['workflows']);
        }

        if (array_key_exists('router', $config)) {
            $this->_usedProperties['router'] = true;
            $this->router = \is_array($config['router']) ? new \Symfony\Config\Framework\RouterConfig($config['router']) : $config['router'];
            unset($config['router']);
        }

        if (array_key_exists('session', $config)) {
            $this->_usedProperties['session'] = true;
            $this->session = \is_array($config['session']) ? new \Symfony\Config\Framework\SessionConfig($config['session']) : $config['session'];
            unset($config['session']);
        }

        if (array_key_exists('request', $config)) {
            $this->_usedProperties['request'] = true;
            $this->request = \is_array($config['request']) ? new \Symfony\Config\Framework\RequestConfig($config['request']) : $config['request'];
            unset($config['request']);
        }

        if (array_key_exists('assets', $config)) {
            $this->_usedProperties['assets'] = true;
            $this->assets = \is_array($config['assets']) ? new \Symfony\Config\Framework\AssetsConfig($config['assets']) : $config['assets'];
            unset($config['assets']);
        }

        if (array_key_exists('asset_mapper', $config)) {
            $this->_usedProperties['assetMapper'] = true;
            $this->assetMapper = \is_array($config['asset_mapper']) ? new \Symfony\Config\Framework\AssetMapperConfig($config['asset_mapper']) : $config['asset_mapper'];
            unset($config['asset_mapper']);
        }

        if (array_key_exists('translator', $config)) {
            $this->_usedProperties['translator'] = true;
            $this->translator = \is_array($config['translator']) ? new \Symfony\Config\Framework\TranslatorConfig($config['translator']) : $config['translator'];
            unset($config['translator']);
        }

        if (array_key_exists('validation', $config)) {
            $this->_usedProperties['validation'] = true;
            $this->validation = \is_array($config['validation']) ? new \Symfony\Config\Framework\ValidationConfig($config['validation']) : $config['validation'];
            unset($config['validation']);
        }

        if (array_key_exists('annotations', $config)) {
            $this->_usedProperties['annotations'] = true;
            $this->annotations = \is_array($config['annotations']) ? new \Symfony\Config\Framework\AnnotationsConfig($config['annotations']) : $config['annotations'];
            unset($config['annotations']);
        }

        if (array_key_exists('serializer', $config)) {
            $this->_usedProperties['serializer'] = true;
            $this->serializer = \is_array($config['serializer']) ? new \Symfony\Config\Framework\SerializerConfig($config['serializer']) : $config['serializer'];
            unset($config['serializer']);
        }

        if (array_key_exists('property_access', $config)) {
            $this->_usedProperties['propertyAccess'] = true;
            $this->propertyAccess = \is_array($config['property_access']) ? new \Symfony\Config\Framework\PropertyAccessConfig($config['property_access']) : $config['property_access'];
            unset($config['property_access']);
        }

        if (array_key_exists('type_info', $config)) {
            $this->_usedProperties['typeInfo'] = true;
            $this->typeInfo = \is_array($config['type_info']) ? new \Symfony\Config\Framework\TypeInfoConfig($config['type_info']) : $config['type_info'];
            unset($config['type_info']);
        }

        if (array_key_exists('property_info', $config)) {
            $this->_usedProperties['propertyInfo'] = true;
            $this->propertyInfo = \is_array($config['property_info']) ? new \Symfony\Config\Framework\PropertyInfoConfig($config['property_info']) : $config['property_info'];
            unset($config['property_info']);
        }

        if (array_key_exists('cache', $config)) {
            $this->_usedProperties['cache'] = true;
            $this->cache = new \Symfony\Config\Framework\CacheConfig($config['cache']);
            unset($config['cache']);
        }

        if (array_key_exists('php_errors', $config)) {
            $this->_usedProperties['phpErrors'] = true;
            $this->phpErrors = new \Symfony\Config\Framework\PhpErrorsConfig($config['php_errors']);
            unset($config['php_errors']);
        }

        if (array_key_exists('exceptions', $config)) {
            $this->_usedProperties['exceptions'] = true;
            $this->exceptions = array_map(fn ($v) => new \Symfony\Config\Framework\ExceptionConfig($v), $config['exceptions']);
            unset($config['exceptions']);
        }

        if (array_key_exists('web_link', $config)) {
            $this->_usedProperties['webLink'] = true;
            $this->webLink = \is_array($config['web_link']) ? new \Symfony\Config\Framework\WebLinkConfig($config['web_link']) : $config['web_link'];
            unset($config['web_link']);
        }

        if (array_key_exists('lock', $config)) {
            $this->_usedProperties['lock'] = true;
            $this->lock = \is_array($config['lock']) ? new \Symfony\Config\Framework\LockConfig($config['lock']) : $config['lock'];
            unset($config['lock']);
        }

        if (array_key_exists('semaphore', $config)) {
            $this->_usedProperties['semaphore'] = true;
            $this->semaphore = \is_array($config['semaphore']) ? new \Symfony\Config\Framework\SemaphoreConfig($config['semaphore']) : $config['semaphore'];
            unset($config['semaphore']);
        }

        if (array_key_exists('messenger', $config)) {
            $this->_usedProperties['messenger'] = true;
            $this->messenger = \is_array($config['messenger']) ? new \Symfony\Config\Framework\MessengerConfig($config['messenger']) : $config['messenger'];
            unset($config['messenger']);
        }

        if (array_key_exists('scheduler', $config)) {
            $this->_usedProperties['scheduler'] = true;
            $this->scheduler = \is_array($config['scheduler']) ? new \Symfony\Config\Framework\SchedulerConfig($config['scheduler']) : $config['scheduler'];
            unset($config['scheduler']);
        }

        if (array_key_exists('disallow_search_engine_index', $config)) {
            $this->_usedProperties['disallowSearchEngineIndex'] = true;
            $this->disallowSearchEngineIndex = $config['disallow_search_engine_index'];
            unset($config['disallow_search_engine_index']);
        }

        if (array_key_exists('http_client', $config)) {
            $this->_usedProperties['httpClient'] = true;
            $this->httpClient = \is_array($config['http_client']) ? new \Symfony\Config\Framework\HttpClientConfig($config['http_client']) : $config['http_client'];
            unset($config['http_client']);
        }

        if (array_key_exists('mailer', $config)) {
            $this->_usedProperties['mailer'] = true;
            $this->mailer = \is_array($config['mailer']) ? new \Symfony\Config\Framework\MailerConfig($config['mailer']) : $config['mailer'];
            unset($config['mailer']);
        }

        if (array_key_exists('secrets', $config)) {
            $this->_usedProperties['secrets'] = true;
            $this->secrets = \is_array($config['secrets']) ? new \Symfony\Config\Framework\SecretsConfig($config['secrets']) : $config['secrets'];
            unset($config['secrets']);
        }

        if (array_key_exists('notifier', $config)) {
            $this->_usedProperties['notifier'] = true;
            $this->notifier = \is_array($config['notifier']) ? new \Symfony\Config\Framework\NotifierConfig($config['notifier']) : $config['notifier'];
            unset($config['notifier']);
        }

        if (array_key_exists('rate_limiter', $config)) {
            $this->_usedProperties['rateLimiter'] = true;
            $this->rateLimiter = \is_array($config['rate_limiter']) ? new \Symfony\Config\Framework\RateLimiterConfig($config['rate_limiter']) : $config['rate_limiter'];
            unset($config['rate_limiter']);
        }

        if (array_key_exists('uid', $config)) {
            $this->_usedProperties['uid'] = true;
            $this->uid = \is_array($config['uid']) ? new \Symfony\Config\Framework\UidConfig($config['uid']) : $config['uid'];
            unset($config['uid']);
        }

        if (array_key_exists('html_sanitizer', $config)) {
            $this->_usedProperties['htmlSanitizer'] = true;
            $this->htmlSanitizer = \is_array($config['html_sanitizer']) ? new \Symfony\Config\Framework\HtmlSanitizerConfig($config['html_sanitizer']) : $config['html_sanitizer'];
            unset($config['html_sanitizer']);
        }

        if (array_key_exists('webhook', $config)) {
            $this->_usedProperties['webhook'] = true;
            $this->webhook = \is_array($config['webhook']) ? new \Symfony\Config\Framework\WebhookConfig($config['webhook']) : $config['webhook'];
            unset($config['webhook']);
        }

        if (array_key_exists('remote-event', $config)) {
            $this->_usedProperties['remoteevent'] = true;
            $this->remoteevent = \is_array($config['remote-event']) ? new \Symfony\Config\Framework\RemoteeventConfig($config['remote-event']) : $config['remote-event'];
            unset($config['remote-event']);
        }

        if (array_key_exists('json_streamer', $config)) {
            $this->_usedProperties['jsonStreamer'] = true;
            $this->jsonStreamer = \is_array($config['json_streamer']) ? new \Symfony\Config\Framework\JsonStreamerConfig($config['json_streamer']) : $config['json_streamer'];
            unset($config['json_streamer']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['secret'])) {
            $output['secret'] = $this->secret;
        }
        if (isset($this->_usedProperties['httpMethodOverride'])) {
            $output['http_method_override'] = $this->httpMethodOverride;
        }
        if (isset($this->_usedProperties['trustXSendfileTypeHeader'])) {
            $output['trust_x_sendfile_type_header'] = $this->trustXSendfileTypeHeader;
        }
        if (isset($this->_usedProperties['ide'])) {
            $output['ide'] = $this->ide;
        }
        if (isset($this->_usedProperties['test'])) {
            $output['test'] = $this->test;
        }
        if (isset($this->_usedProperties['defaultLocale'])) {
            $output['default_locale'] = $this->defaultLocale;
        }
        if (isset($this->_usedProperties['setLocaleFromAcceptLanguage'])) {
            $output['set_locale_from_accept_language'] = $this->setLocaleFromAcceptLanguage;
        }
        if (isset($this->_usedProperties['setContentLanguageFromLocale'])) {
            $output['set_content_language_from_locale'] = $this->setContentLanguageFromLocale;
        }
        if (isset($this->_usedProperties['enabledLocales'])) {
            $output['enabled_locales'] = $this->enabledLocales;
        }
        if (isset($this->_usedProperties['trustedHosts'])) {
            $output['trusted_hosts'] = $this->trustedHosts;
        }
        if (isset($this->_usedProperties['trustedProxies'])) {
            $output['trusted_proxies'] = $this->trustedProxies;
        }
        if (isset($this->_usedProperties['trustedHeaders'])) {
            $output['trusted_headers'] = $this->trustedHeaders;
        }
        if (isset($this->_usedProperties['errorController'])) {
            $output['error_controller'] = $this->errorController;
        }
        if (isset($this->_usedProperties['handleAllThrowables'])) {
            $output['handle_all_throwables'] = $this->handleAllThrowables;
        }
        if (isset($this->_usedProperties['csrfProtection'])) {
            $output['csrf_protection'] = $this->csrfProtection instanceof \Symfony\Config\Framework\CsrfProtectionConfig ? $this->csrfProtection->toArray() : $this->csrfProtection;
        }
        if (isset($this->_usedProperties['form'])) {
            $output['form'] = $this->form instanceof \Symfony\Config\Framework\FormConfig ? $this->form->toArray() : $this->form;
        }
        if (isset($this->_usedProperties['httpCache'])) {
            $output['http_cache'] = $this->httpCache instanceof \Symfony\Config\Framework\HttpCacheConfig ? $this->httpCache->toArray() : $this->httpCache;
        }
        if (isset($this->_usedProperties['esi'])) {
            $output['esi'] = $this->esi instanceof \Symfony\Config\Framework\EsiConfig ? $this->esi->toArray() : $this->esi;
        }
        if (isset($this->_usedProperties['ssi'])) {
            $output['ssi'] = $this->ssi instanceof \Symfony\Config\Framework\SsiConfig ? $this->ssi->toArray() : $this->ssi;
        }
        if (isset($this->_usedProperties['fragments'])) {
            $output['fragments'] = $this->fragments instanceof \Symfony\Config\Framework\FragmentsConfig ? $this->fragments->toArray() : $this->fragments;
        }
        if (isset($this->_usedProperties['profiler'])) {
            $output['profiler'] = $this->profiler instanceof \Symfony\Config\Framework\ProfilerConfig ? $this->profiler->toArray() : $this->profiler;
        }
        if (isset($this->_usedProperties['workflows'])) {
            $output['workflows'] = $this->workflows instanceof \Symfony\Config\Framework\WorkflowsConfig ? $this->workflows->toArray() : $this->workflows;
        }
        if (isset($this->_usedProperties['router'])) {
            $output['router'] = $this->router instanceof \Symfony\Config\Framework\RouterConfig ? $this->router->toArray() : $this->router;
        }
        if (isset($this->_usedProperties['session'])) {
            $output['session'] = $this->session instanceof \Symfony\Config\Framework\SessionConfig ? $this->session->toArray() : $this->session;
        }
        if (isset($this->_usedProperties['request'])) {
            $output['request'] = $this->request instanceof \Symfony\Config\Framework\RequestConfig ? $this->request->toArray() : $this->request;
        }
        if (isset($this->_usedProperties['assets'])) {
            $output['assets'] = $this->assets instanceof \Symfony\Config\Framework\AssetsConfig ? $this->assets->toArray() : $this->assets;
        }
        if (isset($this->_usedProperties['assetMapper'])) {
            $output['asset_mapper'] = $this->assetMapper instanceof \Symfony\Config\Framework\AssetMapperConfig ? $this->assetMapper->toArray() : $this->assetMapper;
        }
        if (isset($this->_usedProperties['translator'])) {
            $output['translator'] = $this->translator instanceof \Symfony\Config\Framework\TranslatorConfig ? $this->translator->toArray() : $this->translator;
        }
        if (isset($this->_usedProperties['validation'])) {
            $output['validation'] = $this->validation instanceof \Symfony\Config\Framework\ValidationConfig ? $this->validation->toArray() : $this->validation;
        }
        if (isset($this->_usedProperties['annotations'])) {
            $output['annotations'] = $this->annotations instanceof \Symfony\Config\Framework\AnnotationsConfig ? $this->annotations->toArray() : $this->annotations;
        }
        if (isset($this->_usedProperties['serializer'])) {
            $output['serializer'] = $this->serializer instanceof \Symfony\Config\Framework\SerializerConfig ? $this->serializer->toArray() : $this->serializer;
        }
        if (isset($this->_usedProperties['propertyAccess'])) {
            $output['property_access'] = $this->propertyAccess instanceof \Symfony\Config\Framework\PropertyAccessConfig ? $this->propertyAccess->toArray() : $this->propertyAccess;
        }
        if (isset($this->_usedProperties['typeInfo'])) {
            $output['type_info'] = $this->typeInfo instanceof \Symfony\Config\Framework\TypeInfoConfig ? $this->typeInfo->toArray() : $this->typeInfo;
        }
        if (isset($this->_usedProperties['propertyInfo'])) {
            $output['property_info'] = $this->propertyInfo instanceof \Symfony\Config\Framework\PropertyInfoConfig ? $this->propertyInfo->toArray() : $this->propertyInfo;
        }
        if (isset($this->_usedProperties['cache'])) {
            $output['cache'] = $this->cache->toArray();
        }
        if (isset($this->_usedProperties['phpErrors'])) {
            $output['php_errors'] = $this->phpErrors->toArray();
        }
        if (isset($this->_usedProperties['exceptions'])) {
            $output['exceptions'] = array_map(fn ($v) => $v->toArray(), $this->exceptions);
        }
        if (isset($this->_usedProperties['webLink'])) {
            $output['web_link'] = $this->webLink instanceof \Symfony\Config\Framework\WebLinkConfig ? $this->webLink->toArray() : $this->webLink;
        }
        if (isset($this->_usedProperties['lock'])) {
            $output['lock'] = $this->lock instanceof \Symfony\Config\Framework\LockConfig ? $this->lock->toArray() : $this->lock;
        }
        if (isset($this->_usedProperties['semaphore'])) {
            $output['semaphore'] = $this->semaphore instanceof \Symfony\Config\Framework\SemaphoreConfig ? $this->semaphore->toArray() : $this->semaphore;
        }
        if (isset($this->_usedProperties['messenger'])) {
            $output['messenger'] = $this->messenger instanceof \Symfony\Config\Framework\MessengerConfig ? $this->messenger->toArray() : $this->messenger;
        }
        if (isset($this->_usedProperties['scheduler'])) {
            $output['scheduler'] = $this->scheduler instanceof \Symfony\Config\Framework\SchedulerConfig ? $this->scheduler->toArray() : $this->scheduler;
        }
        if (isset($this->_usedProperties['disallowSearchEngineIndex'])) {
            $output['disallow_search_engine_index'] = $this->disallowSearchEngineIndex;
        }
        if (isset($this->_usedProperties['httpClient'])) {
            $output['http_client'] = $this->httpClient instanceof \Symfony\Config\Framework\HttpClientConfig ? $this->httpClient->toArray() : $this->httpClient;
        }
        if (isset($this->_usedProperties['mailer'])) {
            $output['mailer'] = $this->mailer instanceof \Symfony\Config\Framework\MailerConfig ? $this->mailer->toArray() : $this->mailer;
        }
        if (isset($this->_usedProperties['secrets'])) {
            $output['secrets'] = $this->secrets instanceof \Symfony\Config\Framework\SecretsConfig ? $this->secrets->toArray() : $this->secrets;
        }
        if (isset($this->_usedProperties['notifier'])) {
            $output['notifier'] = $this->notifier instanceof \Symfony\Config\Framework\NotifierConfig ? $this->notifier->toArray() : $this->notifier;
        }
        if (isset($this->_usedProperties['rateLimiter'])) {
            $output['rate_limiter'] = $this->rateLimiter instanceof \Symfony\Config\Framework\RateLimiterConfig ? $this->rateLimiter->toArray() : $this->rateLimiter;
        }
        if (isset($this->_usedProperties['uid'])) {
            $output['uid'] = $this->uid instanceof \Symfony\Config\Framework\UidConfig ? $this->uid->toArray() : $this->uid;
        }
        if (isset($this->_usedProperties['htmlSanitizer'])) {
            $output['html_sanitizer'] = $this->htmlSanitizer instanceof \Symfony\Config\Framework\HtmlSanitizerConfig ? $this->htmlSanitizer->toArray() : $this->htmlSanitizer;
        }
        if (isset($this->_usedProperties['webhook'])) {
            $output['webhook'] = $this->webhook instanceof \Symfony\Config\Framework\WebhookConfig ? $this->webhook->toArray() : $this->webhook;
        }
        if (isset($this->_usedProperties['remoteevent'])) {
            $output['remote-event'] = $this->remoteevent instanceof \Symfony\Config\Framework\RemoteeventConfig ? $this->remoteevent->toArray() : $this->remoteevent;
        }
        if (isset($this->_usedProperties['jsonStreamer'])) {
            $output['json_streamer'] = $this->jsonStreamer instanceof \Symfony\Config\Framework\JsonStreamerConfig ? $this->jsonStreamer->toArray() : $this->jsonStreamer;
        }
        if ($this->_hasDeprecatedCalls) {
            trigger_deprecation('symfony/config', '7.4', 'Calling any fluent method on "%s" is deprecated; pass the configuration to the constructor instead.', $this::class);
        }

        return $output;
    }

}
