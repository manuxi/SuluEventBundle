<?php

namespace Symfony\Config;

require_once __DIR__.\DIRECTORY_SEPARATOR.'SuluMarkup'.\DIRECTORY_SEPARATOR.'LinkTagConfig.php';

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class SuluMarkupConfig implements \Symfony\Component\Config\Builder\ConfigBuilderInterface
{
    private $linkTag;
    private $_usedProperties = [];
    private $_hasDeprecatedCalls = false;

    /**
     * @default {"provider_attribute":null}
     * @deprecated since Symfony 7.4
     */
    public function linkTag(array $value = []): \Symfony\Config\SuluMarkup\LinkTagConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (null === $this->linkTag) {
            $this->_usedProperties['linkTag'] = true;
            $this->linkTag = new \Symfony\Config\SuluMarkup\LinkTagConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "linkTag()" has already been initialized. You cannot pass values the second time you call linkTag().');
        }

        return $this->linkTag;
    }

    public function getExtensionAlias(): string
    {
        return 'sulu_markup';
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('link_tag', $config)) {
            $this->_usedProperties['linkTag'] = true;
            $this->linkTag = new \Symfony\Config\SuluMarkup\LinkTagConfig($config['link_tag']);
            unset($config['link_tag']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['linkTag'])) {
            $output['link_tag'] = $this->linkTag->toArray();
        }
        if ($this->_hasDeprecatedCalls) {
            trigger_deprecation('symfony/config', '7.4', 'Calling any fluent method on "%s" is deprecated; pass the configuration to the constructor instead.', $this::class);
        }

        return $output;
    }

}
