<?php

namespace Symfony\Config\Framework;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class CsrfProtectionConfig 
{
    private $enabled;
    private $statelessTokenIds;
    private $checkHeader;
    private $cookieName;
    private $_usedProperties = [];

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function enabled($value): static
    {
        $this->_usedProperties['enabled'] = true;
        $this->enabled = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed> $value
     *
     * @return $this
     */
    public function statelessTokenIds(ParamConfigurator|array $value): static
    {
        $this->_usedProperties['statelessTokenIds'] = true;
        $this->statelessTokenIds = $value;

        return $this;
    }

    /**
     * Whether to check the CSRF token in a header in addition to a cookie when using stateless protection.
     * @default false
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function checkHeader($value): static
    {
        $this->_usedProperties['checkHeader'] = true;
        $this->checkHeader = $value;

        return $this;
    }

    /**
     * The name of the cookie to use when using stateless protection.
     * @default 'csrf-token'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function cookieName($value): static
    {
        $this->_usedProperties['cookieName'] = true;
        $this->cookieName = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('enabled', $config)) {
            $this->_usedProperties['enabled'] = true;
            $this->enabled = $config['enabled'];
            unset($config['enabled']);
        }

        if (array_key_exists('stateless_token_ids', $config)) {
            $this->_usedProperties['statelessTokenIds'] = true;
            $this->statelessTokenIds = $config['stateless_token_ids'];
            unset($config['stateless_token_ids']);
        }

        if (array_key_exists('check_header', $config)) {
            $this->_usedProperties['checkHeader'] = true;
            $this->checkHeader = $config['check_header'];
            unset($config['check_header']);
        }

        if (array_key_exists('cookie_name', $config)) {
            $this->_usedProperties['cookieName'] = true;
            $this->cookieName = $config['cookie_name'];
            unset($config['cookie_name']);
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
        if (isset($this->_usedProperties['statelessTokenIds'])) {
            $output['stateless_token_ids'] = $this->statelessTokenIds;
        }
        if (isset($this->_usedProperties['checkHeader'])) {
            $output['check_header'] = $this->checkHeader;
        }
        if (isset($this->_usedProperties['cookieName'])) {
            $output['cookie_name'] = $this->cookieName;
        }

        return $output;
    }

}
