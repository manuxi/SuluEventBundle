<?php

namespace Symfony\Config\Framework\HtmlSanitizer;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class SanitizerConfig 
{
    private $allowSafeElements;
    private $allowStaticElements;
    private $allowElements;
    private $blockElements;
    private $dropElements;
    private $allowAttributes;
    private $dropAttributes;
    private $forceAttributes;
    private $forceHttpsUrls;
    private $allowedLinkSchemes;
    private $allowedLinkHosts;
    private $allowRelativeLinks;
    private $allowedMediaSchemes;
    private $allowedMediaHosts;
    private $allowRelativeMedias;
    private $withAttributeSanitizers;
    private $withoutAttributeSanitizers;
    private $maxInputLength;
    private $_usedProperties = [];

    /**
     * Allows "safe" elements and attributes.
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function allowSafeElements($value): static
    {
        $this->_usedProperties['allowSafeElements'] = true;
        $this->allowSafeElements = $value;

        return $this;
    }

    /**
     * Allows all static elements and attributes from the W3C Sanitizer API standard.
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function allowStaticElements($value): static
    {
        $this->_usedProperties['allowStaticElements'] = true;
        $this->allowStaticElements = $value;

        return $this;
    }

    /**
     * @return $this
     */
    public function allowElement(string $name, mixed $value): static
    {
        $this->_usedProperties['allowElements'] = true;
        $this->allowElements[$name] = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed>|mixed $value
     *
     * @return $this
     */
    public function blockElements(mixed $value): static
    {
        $this->_usedProperties['blockElements'] = true;
        $this->blockElements = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed>|mixed $value
     *
     * @return $this
     */
    public function dropElements(mixed $value): static
    {
        $this->_usedProperties['dropElements'] = true;
        $this->dropElements = $value;

        return $this;
    }

    /**
     * @return $this
     */
    public function allowAttribute(string $name, mixed $value): static
    {
        $this->_usedProperties['allowAttributes'] = true;
        $this->allowAttributes[$name] = $value;

        return $this;
    }

    /**
     * @return $this
     */
    public function dropAttribute(string $name, mixed $value): static
    {
        $this->_usedProperties['dropAttributes'] = true;
        $this->dropAttributes[$name] = $value;

        return $this;
    }

    /**
     * @return $this
     */
    public function forceAttribute(string $name, ParamConfigurator|array $value): static
    {
        $this->_usedProperties['forceAttributes'] = true;
        $this->forceAttributes[$name] = $value;

        return $this;
    }

    /**
     * Transforms URLs using the HTTP scheme to use the HTTPS scheme instead.
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function forceHttpsUrls($value): static
    {
        $this->_usedProperties['forceHttpsUrls'] = true;
        $this->forceHttpsUrls = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed> $value
     *
     * @return $this
     */
    public function allowedLinkSchemes(ParamConfigurator|array $value): static
    {
        $this->_usedProperties['allowedLinkSchemes'] = true;
        $this->allowedLinkSchemes = $value;

        return $this;
    }

    /**
     * Allows only a given list of hosts to be used in links href attributes.
     * @default null
     * @param ParamConfigurator|mixed $value
     *
     * @return $this
     */
    public function allowedLinkHosts(mixed $value = NULL): static
    {
        $this->_usedProperties['allowedLinkHosts'] = true;
        $this->allowedLinkHosts = $value;

        return $this;
    }

    /**
     * Allows relative URLs to be used in links href attributes.
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function allowRelativeLinks($value): static
    {
        $this->_usedProperties['allowRelativeLinks'] = true;
        $this->allowRelativeLinks = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed> $value
     *
     * @return $this
     */
    public function allowedMediaSchemes(ParamConfigurator|array $value): static
    {
        $this->_usedProperties['allowedMediaSchemes'] = true;
        $this->allowedMediaSchemes = $value;

        return $this;
    }

    /**
     * Allows only a given list of hosts to be used in media source attributes (img, audio, video, ...).
     * @default null
     * @param ParamConfigurator|mixed $value
     *
     * @return $this
     */
    public function allowedMediaHosts(mixed $value = NULL): static
    {
        $this->_usedProperties['allowedMediaHosts'] = true;
        $this->allowedMediaHosts = $value;

        return $this;
    }

    /**
     * Allows relative URLs to be used in media source attributes (img, audio, video, ...).
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function allowRelativeMedias($value): static
    {
        $this->_usedProperties['allowRelativeMedias'] = true;
        $this->allowRelativeMedias = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed> $value
     *
     * @return $this
     */
    public function withAttributeSanitizers(ParamConfigurator|array $value): static
    {
        $this->_usedProperties['withAttributeSanitizers'] = true;
        $this->withAttributeSanitizers = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed> $value
     *
     * @return $this
     */
    public function withoutAttributeSanitizers(ParamConfigurator|array $value): static
    {
        $this->_usedProperties['withoutAttributeSanitizers'] = true;
        $this->withoutAttributeSanitizers = $value;

        return $this;
    }

    /**
     * The maximum length allowed for the sanitized input.
     * @default 0
     * @param ParamConfigurator|int $value
     * @return $this
     */
    public function maxInputLength($value): static
    {
        $this->_usedProperties['maxInputLength'] = true;
        $this->maxInputLength = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('allow_safe_elements', $config)) {
            $this->_usedProperties['allowSafeElements'] = true;
            $this->allowSafeElements = $config['allow_safe_elements'];
            unset($config['allow_safe_elements']);
        }

        if (array_key_exists('allow_static_elements', $config)) {
            $this->_usedProperties['allowStaticElements'] = true;
            $this->allowStaticElements = $config['allow_static_elements'];
            unset($config['allow_static_elements']);
        }

        if (array_key_exists('allow_elements', $config)) {
            $this->_usedProperties['allowElements'] = true;
            $this->allowElements = $config['allow_elements'];
            unset($config['allow_elements']);
        }

        if (array_key_exists('block_elements', $config)) {
            $this->_usedProperties['blockElements'] = true;
            $this->blockElements = $config['block_elements'];
            unset($config['block_elements']);
        }

        if (array_key_exists('drop_elements', $config)) {
            $this->_usedProperties['dropElements'] = true;
            $this->dropElements = $config['drop_elements'];
            unset($config['drop_elements']);
        }

        if (array_key_exists('allow_attributes', $config)) {
            $this->_usedProperties['allowAttributes'] = true;
            $this->allowAttributes = $config['allow_attributes'];
            unset($config['allow_attributes']);
        }

        if (array_key_exists('drop_attributes', $config)) {
            $this->_usedProperties['dropAttributes'] = true;
            $this->dropAttributes = $config['drop_attributes'];
            unset($config['drop_attributes']);
        }

        if (array_key_exists('force_attributes', $config)) {
            $this->_usedProperties['forceAttributes'] = true;
            $this->forceAttributes = $config['force_attributes'];
            unset($config['force_attributes']);
        }

        if (array_key_exists('force_https_urls', $config)) {
            $this->_usedProperties['forceHttpsUrls'] = true;
            $this->forceHttpsUrls = $config['force_https_urls'];
            unset($config['force_https_urls']);
        }

        if (array_key_exists('allowed_link_schemes', $config)) {
            $this->_usedProperties['allowedLinkSchemes'] = true;
            $this->allowedLinkSchemes = $config['allowed_link_schemes'];
            unset($config['allowed_link_schemes']);
        }

        if (array_key_exists('allowed_link_hosts', $config)) {
            $this->_usedProperties['allowedLinkHosts'] = true;
            $this->allowedLinkHosts = $config['allowed_link_hosts'];
            unset($config['allowed_link_hosts']);
        }

        if (array_key_exists('allow_relative_links', $config)) {
            $this->_usedProperties['allowRelativeLinks'] = true;
            $this->allowRelativeLinks = $config['allow_relative_links'];
            unset($config['allow_relative_links']);
        }

        if (array_key_exists('allowed_media_schemes', $config)) {
            $this->_usedProperties['allowedMediaSchemes'] = true;
            $this->allowedMediaSchemes = $config['allowed_media_schemes'];
            unset($config['allowed_media_schemes']);
        }

        if (array_key_exists('allowed_media_hosts', $config)) {
            $this->_usedProperties['allowedMediaHosts'] = true;
            $this->allowedMediaHosts = $config['allowed_media_hosts'];
            unset($config['allowed_media_hosts']);
        }

        if (array_key_exists('allow_relative_medias', $config)) {
            $this->_usedProperties['allowRelativeMedias'] = true;
            $this->allowRelativeMedias = $config['allow_relative_medias'];
            unset($config['allow_relative_medias']);
        }

        if (array_key_exists('with_attribute_sanitizers', $config)) {
            $this->_usedProperties['withAttributeSanitizers'] = true;
            $this->withAttributeSanitizers = $config['with_attribute_sanitizers'];
            unset($config['with_attribute_sanitizers']);
        }

        if (array_key_exists('without_attribute_sanitizers', $config)) {
            $this->_usedProperties['withoutAttributeSanitizers'] = true;
            $this->withoutAttributeSanitizers = $config['without_attribute_sanitizers'];
            unset($config['without_attribute_sanitizers']);
        }

        if (array_key_exists('max_input_length', $config)) {
            $this->_usedProperties['maxInputLength'] = true;
            $this->maxInputLength = $config['max_input_length'];
            unset($config['max_input_length']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['allowSafeElements'])) {
            $output['allow_safe_elements'] = $this->allowSafeElements;
        }
        if (isset($this->_usedProperties['allowStaticElements'])) {
            $output['allow_static_elements'] = $this->allowStaticElements;
        }
        if (isset($this->_usedProperties['allowElements'])) {
            $output['allow_elements'] = $this->allowElements;
        }
        if (isset($this->_usedProperties['blockElements'])) {
            $output['block_elements'] = $this->blockElements;
        }
        if (isset($this->_usedProperties['dropElements'])) {
            $output['drop_elements'] = $this->dropElements;
        }
        if (isset($this->_usedProperties['allowAttributes'])) {
            $output['allow_attributes'] = $this->allowAttributes;
        }
        if (isset($this->_usedProperties['dropAttributes'])) {
            $output['drop_attributes'] = $this->dropAttributes;
        }
        if (isset($this->_usedProperties['forceAttributes'])) {
            $output['force_attributes'] = $this->forceAttributes;
        }
        if (isset($this->_usedProperties['forceHttpsUrls'])) {
            $output['force_https_urls'] = $this->forceHttpsUrls;
        }
        if (isset($this->_usedProperties['allowedLinkSchemes'])) {
            $output['allowed_link_schemes'] = $this->allowedLinkSchemes;
        }
        if (isset($this->_usedProperties['allowedLinkHosts'])) {
            $output['allowed_link_hosts'] = $this->allowedLinkHosts;
        }
        if (isset($this->_usedProperties['allowRelativeLinks'])) {
            $output['allow_relative_links'] = $this->allowRelativeLinks;
        }
        if (isset($this->_usedProperties['allowedMediaSchemes'])) {
            $output['allowed_media_schemes'] = $this->allowedMediaSchemes;
        }
        if (isset($this->_usedProperties['allowedMediaHosts'])) {
            $output['allowed_media_hosts'] = $this->allowedMediaHosts;
        }
        if (isset($this->_usedProperties['allowRelativeMedias'])) {
            $output['allow_relative_medias'] = $this->allowRelativeMedias;
        }
        if (isset($this->_usedProperties['withAttributeSanitizers'])) {
            $output['with_attribute_sanitizers'] = $this->withAttributeSanitizers;
        }
        if (isset($this->_usedProperties['withoutAttributeSanitizers'])) {
            $output['without_attribute_sanitizers'] = $this->withoutAttributeSanitizers;
        }
        if (isset($this->_usedProperties['maxInputLength'])) {
            $output['max_input_length'] = $this->maxInputLength;
        }

        return $output;
    }

}
