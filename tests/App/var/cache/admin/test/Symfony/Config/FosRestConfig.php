<?php

namespace Symfony\Config;

require_once __DIR__.\DIRECTORY_SEPARATOR.'FosRest'.\DIRECTORY_SEPARATOR.'ParamFetcherListenerConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'FosRest'.\DIRECTORY_SEPARATOR.'AllowedMethodsListenerConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'FosRest'.\DIRECTORY_SEPARATOR.'BodyConverterConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'FosRest'.\DIRECTORY_SEPARATOR.'ServiceConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'FosRest'.\DIRECTORY_SEPARATOR.'SerializerConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'FosRest'.\DIRECTORY_SEPARATOR.'ZoneConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'FosRest'.\DIRECTORY_SEPARATOR.'ViewConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'FosRest'.\DIRECTORY_SEPARATOR.'ExceptionConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'FosRest'.\DIRECTORY_SEPARATOR.'BodyListenerConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'FosRest'.\DIRECTORY_SEPARATOR.'FormatListenerConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'FosRest'.\DIRECTORY_SEPARATOR.'VersioningConfig.php';

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class FosRestConfig implements \Symfony\Component\Config\Builder\ConfigBuilderInterface
{
    private $disableCsrfRole;
    private $unauthorizedChallenge;
    private $paramFetcherListener;
    private $cacheDir;
    private $allowedMethodsListener;
    private $routingLoader;
    private $bodyConverter;
    private $service;
    private $serializer;
    private $zone;
    private $view;
    private $exception;
    private $bodyListener;
    private $formatListener;
    private $versioning;
    private $_usedProperties = [];
    private $_hasDeprecatedCalls = false;

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function disableCsrfRole($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['disableCsrfRole'] = true;
        $this->disableCsrfRole = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function unauthorizedChallenge($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['unauthorizedChallenge'] = true;
        $this->unauthorizedChallenge = $value;

        return $this;
    }

    /**
     * @template TValue of string|array|bool
     * @param TValue $value
     * @default {"enabled":false,"force":false,"service":null}
     * @return \Symfony\Config\FosRest\ParamFetcherListenerConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\FosRest\ParamFetcherListenerConfig : static)
     * @deprecated since Symfony 7.4
     */
    public function paramFetcherListener(string|array|bool $value = []): \Symfony\Config\FosRest\ParamFetcherListenerConfig|static
    {
        $this->_hasDeprecatedCalls = true;
        if (!\is_array($value)) {
            $this->_usedProperties['paramFetcherListener'] = true;
            $this->paramFetcherListener = $value;

            return $this;
        }

        if (!$this->paramFetcherListener instanceof \Symfony\Config\FosRest\ParamFetcherListenerConfig) {
            $this->_usedProperties['paramFetcherListener'] = true;
            $this->paramFetcherListener = new \Symfony\Config\FosRest\ParamFetcherListenerConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "paramFetcherListener()" has already been initialized. You cannot pass values the second time you call paramFetcherListener().');
        }

        return $this->paramFetcherListener;
    }

    /**
     * @default '%kernel.cache_dir%/fos_rest'
     * @param ParamConfigurator|mixed $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function cacheDir($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['cacheDir'] = true;
        $this->cacheDir = $value;

        return $this;
    }

    /**
     * @template TValue of array|bool
     * @param TValue $value
     * @default {"enabled":false,"service":null}
     * @return \Symfony\Config\FosRest\AllowedMethodsListenerConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\FosRest\AllowedMethodsListenerConfig : static)
     * @deprecated since Symfony 7.4
     */
    public function allowedMethodsListener(array|bool $value = []): \Symfony\Config\FosRest\AllowedMethodsListenerConfig|static
    {
        $this->_hasDeprecatedCalls = true;
        if (!\is_array($value)) {
            $this->_usedProperties['allowedMethodsListener'] = true;
            $this->allowedMethodsListener = $value;

            return $this;
        }

        if (!$this->allowedMethodsListener instanceof \Symfony\Config\FosRest\AllowedMethodsListenerConfig) {
            $this->_usedProperties['allowedMethodsListener'] = true;
            $this->allowedMethodsListener = new \Symfony\Config\FosRest\AllowedMethodsListenerConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "allowedMethodsListener()" has already been initialized. You cannot pass values the second time you call allowedMethodsListener().');
        }

        return $this->allowedMethodsListener;
    }

    /**
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function routingLoader($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['routingLoader'] = true;
        $this->routingLoader = $value;

        return $this;
    }

    /**
     * @template TValue of array|bool
     * @param TValue $value
     * @default {"enabled":false,"validate":false,"validation_errors_argument":"validationErrors"}
     * @return \Symfony\Config\FosRest\BodyConverterConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\FosRest\BodyConverterConfig : static)
     * @deprecated since Symfony 7.4
     */
    public function bodyConverter(array|bool $value = []): \Symfony\Config\FosRest\BodyConverterConfig|static
    {
        $this->_hasDeprecatedCalls = true;
        if (!\is_array($value)) {
            $this->_usedProperties['bodyConverter'] = true;
            $this->bodyConverter = $value;

            return $this;
        }

        if (!$this->bodyConverter instanceof \Symfony\Config\FosRest\BodyConverterConfig) {
            $this->_usedProperties['bodyConverter'] = true;
            $this->bodyConverter = new \Symfony\Config\FosRest\BodyConverterConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "bodyConverter()" has already been initialized. You cannot pass values the second time you call bodyConverter().');
        }

        return $this->bodyConverter;
    }

    /**
     * @default {"serializer":null,"view_handler":"fos_rest.view_handler.default","validator":"validator"}
     * @deprecated since Symfony 7.4
     */
    public function service(array $value = []): \Symfony\Config\FosRest\ServiceConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (null === $this->service) {
            $this->_usedProperties['service'] = true;
            $this->service = new \Symfony\Config\FosRest\ServiceConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "service()" has already been initialized. You cannot pass values the second time you call service().');
        }

        return $this->service;
    }

    /**
     * @default {"version":null,"groups":[],"serialize_null":false}
     * @deprecated since Symfony 7.4
     */
    public function serializer(array $value = []): \Symfony\Config\FosRest\SerializerConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (null === $this->serializer) {
            $this->_usedProperties['serializer'] = true;
            $this->serializer = new \Symfony\Config\FosRest\SerializerConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "serializer()" has already been initialized. You cannot pass values the second time you call serializer().');
        }

        return $this->serializer;
    }

    /**
     * @deprecated since Symfony 7.4
     */
    public function zone(array $value = []): \Symfony\Config\FosRest\ZoneConfig
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['zone'] = true;

        return $this->zone[] = new \Symfony\Config\FosRest\ZoneConfig($value);
    }

    /**
     * @default {"mime_types":{"enabled":false,"service":null,"formats":[]},"formats":{"json":true,"xml":true},"view_response_listener":{"enabled":false,"force":false,"service":null},"failed_validation":400,"empty_content":204,"serialize_null":false}
     * @deprecated since Symfony 7.4
     */
    public function view(array $value = []): \Symfony\Config\FosRest\ViewConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (null === $this->view) {
            $this->_usedProperties['view'] = true;
            $this->view = new \Symfony\Config\FosRest\ViewConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "view()" has already been initialized. You cannot pass values the second time you call view().');
        }

        return $this->view;
    }

    /**
     * @template TValue of array|bool
     * @param TValue $value
     * @default {"enabled":false,"map_exception_codes":false,"exception_listener":false,"serialize_exceptions":false,"flatten_exception_format":"legacy","serializer_error_renderer":false,"codes":[],"messages":[],"debug":true}
     * @return \Symfony\Config\FosRest\ExceptionConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\FosRest\ExceptionConfig : static)
     * @deprecated since Symfony 7.4
     */
    public function exception(array|bool $value = []): \Symfony\Config\FosRest\ExceptionConfig|static
    {
        $this->_hasDeprecatedCalls = true;
        if (!\is_array($value)) {
            $this->_usedProperties['exception'] = true;
            $this->exception = $value;

            return $this;
        }

        if (!$this->exception instanceof \Symfony\Config\FosRest\ExceptionConfig) {
            $this->_usedProperties['exception'] = true;
            $this->exception = new \Symfony\Config\FosRest\ExceptionConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "exception()" has already been initialized. You cannot pass values the second time you call exception().');
        }

        return $this->exception;
    }

    /**
     * @template TValue of array|bool
     * @param TValue $value
     * @default {"enabled":false,"service":null,"default_format":null,"throw_exception_on_unsupported_content_type":false,"decoders":{"json":"fos_rest.decoder.json","xml":"fos_rest.decoder.xml"},"array_normalizer":{"service":null,"forms":false}}
     * @return \Symfony\Config\FosRest\BodyListenerConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\FosRest\BodyListenerConfig : static)
     * @deprecated since Symfony 7.4
     */
    public function bodyListener(array|bool $value = []): \Symfony\Config\FosRest\BodyListenerConfig|static
    {
        $this->_hasDeprecatedCalls = true;
        if (!\is_array($value)) {
            $this->_usedProperties['bodyListener'] = true;
            $this->bodyListener = $value;

            return $this;
        }

        if (!$this->bodyListener instanceof \Symfony\Config\FosRest\BodyListenerConfig) {
            $this->_usedProperties['bodyListener'] = true;
            $this->bodyListener = new \Symfony\Config\FosRest\BodyListenerConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "bodyListener()" has already been initialized. You cannot pass values the second time you call bodyListener().');
        }

        return $this->bodyListener;
    }

    /**
     * @template TValue of mixed
     * @param TValue $value
     * @default {"enabled":false,"service":null,"rules":[]}
     * @return \Symfony\Config\FosRest\FormatListenerConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\FosRest\FormatListenerConfig : static)
     * @deprecated since Symfony 7.4
     */
    public function formatListener(mixed $value = []): \Symfony\Config\FosRest\FormatListenerConfig|static
    {
        $this->_hasDeprecatedCalls = true;
        if (!\is_array($value)) {
            $this->_usedProperties['formatListener'] = true;
            $this->formatListener = $value;

            return $this;
        }

        if (!$this->formatListener instanceof \Symfony\Config\FosRest\FormatListenerConfig) {
            $this->_usedProperties['formatListener'] = true;
            $this->formatListener = new \Symfony\Config\FosRest\FormatListenerConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "formatListener()" has already been initialized. You cannot pass values the second time you call formatListener().');
        }

        return $this->formatListener;
    }

    /**
     * @template TValue of array|bool
     * @param TValue $value
     * @default {"enabled":false,"default_version":null,"resolvers":{"query":{"enabled":true,"parameter_name":"version"},"custom_header":{"enabled":true,"header_name":"X-Accept-Version"},"media_type":{"enabled":true,"regex":"\/(v|version)=(?P<version>[0-9\\.]+)\/"}},"guessing_order":["query","custom_header","media_type"]}
     * @return \Symfony\Config\FosRest\VersioningConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\FosRest\VersioningConfig : static)
     * @deprecated since Symfony 7.4
     */
    public function versioning(array|bool $value = []): \Symfony\Config\FosRest\VersioningConfig|static
    {
        $this->_hasDeprecatedCalls = true;
        if (!\is_array($value)) {
            $this->_usedProperties['versioning'] = true;
            $this->versioning = $value;

            return $this;
        }

        if (!$this->versioning instanceof \Symfony\Config\FosRest\VersioningConfig) {
            $this->_usedProperties['versioning'] = true;
            $this->versioning = new \Symfony\Config\FosRest\VersioningConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "versioning()" has already been initialized. You cannot pass values the second time you call versioning().');
        }

        return $this->versioning;
    }

    public function getExtensionAlias(): string
    {
        return 'fos_rest';
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('disable_csrf_role', $config)) {
            $this->_usedProperties['disableCsrfRole'] = true;
            $this->disableCsrfRole = $config['disable_csrf_role'];
            unset($config['disable_csrf_role']);
        }

        if (array_key_exists('unauthorized_challenge', $config)) {
            $this->_usedProperties['unauthorizedChallenge'] = true;
            $this->unauthorizedChallenge = $config['unauthorized_challenge'];
            unset($config['unauthorized_challenge']);
        }

        if (array_key_exists('param_fetcher_listener', $config)) {
            $this->_usedProperties['paramFetcherListener'] = true;
            $this->paramFetcherListener = \is_array($config['param_fetcher_listener']) ? new \Symfony\Config\FosRest\ParamFetcherListenerConfig($config['param_fetcher_listener']) : $config['param_fetcher_listener'];
            unset($config['param_fetcher_listener']);
        }

        if (array_key_exists('cache_dir', $config)) {
            $this->_usedProperties['cacheDir'] = true;
            $this->cacheDir = $config['cache_dir'];
            unset($config['cache_dir']);
        }

        if (array_key_exists('allowed_methods_listener', $config)) {
            $this->_usedProperties['allowedMethodsListener'] = true;
            $this->allowedMethodsListener = \is_array($config['allowed_methods_listener']) ? new \Symfony\Config\FosRest\AllowedMethodsListenerConfig($config['allowed_methods_listener']) : $config['allowed_methods_listener'];
            unset($config['allowed_methods_listener']);
        }

        if (array_key_exists('routing_loader', $config)) {
            $this->_usedProperties['routingLoader'] = true;
            $this->routingLoader = $config['routing_loader'];
            unset($config['routing_loader']);
        }

        if (array_key_exists('body_converter', $config)) {
            $this->_usedProperties['bodyConverter'] = true;
            $this->bodyConverter = \is_array($config['body_converter']) ? new \Symfony\Config\FosRest\BodyConverterConfig($config['body_converter']) : $config['body_converter'];
            unset($config['body_converter']);
        }

        if (array_key_exists('service', $config)) {
            $this->_usedProperties['service'] = true;
            $this->service = new \Symfony\Config\FosRest\ServiceConfig($config['service']);
            unset($config['service']);
        }

        if (array_key_exists('serializer', $config)) {
            $this->_usedProperties['serializer'] = true;
            $this->serializer = new \Symfony\Config\FosRest\SerializerConfig($config['serializer']);
            unset($config['serializer']);
        }

        if (array_key_exists('zone', $config)) {
            $this->_usedProperties['zone'] = true;
            $this->zone = array_map(fn ($v) => new \Symfony\Config\FosRest\ZoneConfig($v), $config['zone']);
            unset($config['zone']);
        }

        if (array_key_exists('view', $config)) {
            $this->_usedProperties['view'] = true;
            $this->view = new \Symfony\Config\FosRest\ViewConfig($config['view']);
            unset($config['view']);
        }

        if (array_key_exists('exception', $config)) {
            $this->_usedProperties['exception'] = true;
            $this->exception = \is_array($config['exception']) ? new \Symfony\Config\FosRest\ExceptionConfig($config['exception']) : $config['exception'];
            unset($config['exception']);
        }

        if (array_key_exists('body_listener', $config)) {
            $this->_usedProperties['bodyListener'] = true;
            $this->bodyListener = \is_array($config['body_listener']) ? new \Symfony\Config\FosRest\BodyListenerConfig($config['body_listener']) : $config['body_listener'];
            unset($config['body_listener']);
        }

        if (array_key_exists('format_listener', $config)) {
            $this->_usedProperties['formatListener'] = true;
            $this->formatListener = \is_array($config['format_listener']) ? new \Symfony\Config\FosRest\FormatListenerConfig($config['format_listener']) : $config['format_listener'];
            unset($config['format_listener']);
        }

        if (array_key_exists('versioning', $config)) {
            $this->_usedProperties['versioning'] = true;
            $this->versioning = \is_array($config['versioning']) ? new \Symfony\Config\FosRest\VersioningConfig($config['versioning']) : $config['versioning'];
            unset($config['versioning']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['disableCsrfRole'])) {
            $output['disable_csrf_role'] = $this->disableCsrfRole;
        }
        if (isset($this->_usedProperties['unauthorizedChallenge'])) {
            $output['unauthorized_challenge'] = $this->unauthorizedChallenge;
        }
        if (isset($this->_usedProperties['paramFetcherListener'])) {
            $output['param_fetcher_listener'] = $this->paramFetcherListener instanceof \Symfony\Config\FosRest\ParamFetcherListenerConfig ? $this->paramFetcherListener->toArray() : $this->paramFetcherListener;
        }
        if (isset($this->_usedProperties['cacheDir'])) {
            $output['cache_dir'] = $this->cacheDir;
        }
        if (isset($this->_usedProperties['allowedMethodsListener'])) {
            $output['allowed_methods_listener'] = $this->allowedMethodsListener instanceof \Symfony\Config\FosRest\AllowedMethodsListenerConfig ? $this->allowedMethodsListener->toArray() : $this->allowedMethodsListener;
        }
        if (isset($this->_usedProperties['routingLoader'])) {
            $output['routing_loader'] = $this->routingLoader;
        }
        if (isset($this->_usedProperties['bodyConverter'])) {
            $output['body_converter'] = $this->bodyConverter instanceof \Symfony\Config\FosRest\BodyConverterConfig ? $this->bodyConverter->toArray() : $this->bodyConverter;
        }
        if (isset($this->_usedProperties['service'])) {
            $output['service'] = $this->service->toArray();
        }
        if (isset($this->_usedProperties['serializer'])) {
            $output['serializer'] = $this->serializer->toArray();
        }
        if (isset($this->_usedProperties['zone'])) {
            $output['zone'] = array_map(fn ($v) => $v->toArray(), $this->zone);
        }
        if (isset($this->_usedProperties['view'])) {
            $output['view'] = $this->view->toArray();
        }
        if (isset($this->_usedProperties['exception'])) {
            $output['exception'] = $this->exception instanceof \Symfony\Config\FosRest\ExceptionConfig ? $this->exception->toArray() : $this->exception;
        }
        if (isset($this->_usedProperties['bodyListener'])) {
            $output['body_listener'] = $this->bodyListener instanceof \Symfony\Config\FosRest\BodyListenerConfig ? $this->bodyListener->toArray() : $this->bodyListener;
        }
        if (isset($this->_usedProperties['formatListener'])) {
            $output['format_listener'] = $this->formatListener instanceof \Symfony\Config\FosRest\FormatListenerConfig ? $this->formatListener->toArray() : $this->formatListener;
        }
        if (isset($this->_usedProperties['versioning'])) {
            $output['versioning'] = $this->versioning instanceof \Symfony\Config\FosRest\VersioningConfig ? $this->versioning->toArray() : $this->versioning;
        }
        if ($this->_hasDeprecatedCalls) {
            trigger_deprecation('symfony/config', '7.4', 'Calling any fluent method on "%s" is deprecated; pass the configuration to the constructor instead.', $this::class);
        }

        return $output;
    }

}
