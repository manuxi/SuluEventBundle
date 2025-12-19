<?php

namespace Symfony\Config\Framework;

require_once __DIR__.\DIRECTORY_SEPARATOR.'Form'.\DIRECTORY_SEPARATOR.'CsrfProtectionConfig.php';

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class FormConfig 
{
    private $enabled;
    private $csrfProtection;
    private $_usedProperties = [];

    /**
     * @default false
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
     * @default {"enabled":null,"token_id":null,"field_name":"_token","field_attr":{"data-controller":"csrf-protection"}}
     */
    public function csrfProtection(array $value = []): \Symfony\Config\Framework\Form\CsrfProtectionConfig
    {
        if (null === $this->csrfProtection) {
            $this->_usedProperties['csrfProtection'] = true;
            $this->csrfProtection = new \Symfony\Config\Framework\Form\CsrfProtectionConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "csrfProtection()" has already been initialized. You cannot pass values the second time you call csrfProtection().');
        }

        return $this->csrfProtection;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('enabled', $config)) {
            $this->_usedProperties['enabled'] = true;
            $this->enabled = $config['enabled'];
            unset($config['enabled']);
        }

        if (array_key_exists('csrf_protection', $config)) {
            $this->_usedProperties['csrfProtection'] = true;
            $this->csrfProtection = new \Symfony\Config\Framework\Form\CsrfProtectionConfig($config['csrf_protection']);
            unset($config['csrf_protection']);
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
        if (isset($this->_usedProperties['csrfProtection'])) {
            $output['csrf_protection'] = $this->csrfProtection->toArray();
        }

        return $output;
    }

}
