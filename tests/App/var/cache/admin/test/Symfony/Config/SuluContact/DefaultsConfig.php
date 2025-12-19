<?php

namespace Symfony\Config\SuluContact;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class DefaultsConfig 
{
    private $phoneType;
    private $phoneTypeMobile;
    private $phoneTypeIsdn;
    private $emailType;
    private $addressType;
    private $urlType;
    private $faxType;
    private $socialMediaProfileType;
    private $country;
    private $_usedProperties = [];

    /**
     * @default '1'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function phoneType($value): static
    {
        $this->_usedProperties['phoneType'] = true;
        $this->phoneType = $value;

        return $this;
    }

    /**
     * @default '3'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function phoneTypeMobile($value): static
    {
        $this->_usedProperties['phoneTypeMobile'] = true;
        $this->phoneTypeMobile = $value;

        return $this;
    }

    /**
     * @default '1'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function phoneTypeIsdn($value): static
    {
        $this->_usedProperties['phoneTypeIsdn'] = true;
        $this->phoneTypeIsdn = $value;

        return $this;
    }

    /**
     * @default '1'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function emailType($value): static
    {
        $this->_usedProperties['emailType'] = true;
        $this->emailType = $value;

        return $this;
    }

    /**
     * @default '1'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function addressType($value): static
    {
        $this->_usedProperties['addressType'] = true;
        $this->addressType = $value;

        return $this;
    }

    /**
     * @default '1'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function urlType($value): static
    {
        $this->_usedProperties['urlType'] = true;
        $this->urlType = $value;

        return $this;
    }

    /**
     * @default '1'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function faxType($value): static
    {
        $this->_usedProperties['faxType'] = true;
        $this->faxType = $value;

        return $this;
    }

    /**
     * @default '1'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function socialMediaProfileType($value): static
    {
        $this->_usedProperties['socialMediaProfileType'] = true;
        $this->socialMediaProfileType = $value;

        return $this;
    }

    /**
     * @default 'AT'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function country($value): static
    {
        $this->_usedProperties['country'] = true;
        $this->country = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('phoneType', $config)) {
            $this->_usedProperties['phoneType'] = true;
            $this->phoneType = $config['phoneType'];
            unset($config['phoneType']);
        }

        if (array_key_exists('phoneTypeMobile', $config)) {
            $this->_usedProperties['phoneTypeMobile'] = true;
            $this->phoneTypeMobile = $config['phoneTypeMobile'];
            unset($config['phoneTypeMobile']);
        }

        if (array_key_exists('phoneTypeIsdn', $config)) {
            $this->_usedProperties['phoneTypeIsdn'] = true;
            $this->phoneTypeIsdn = $config['phoneTypeIsdn'];
            unset($config['phoneTypeIsdn']);
        }

        if (array_key_exists('emailType', $config)) {
            $this->_usedProperties['emailType'] = true;
            $this->emailType = $config['emailType'];
            unset($config['emailType']);
        }

        if (array_key_exists('addressType', $config)) {
            $this->_usedProperties['addressType'] = true;
            $this->addressType = $config['addressType'];
            unset($config['addressType']);
        }

        if (array_key_exists('urlType', $config)) {
            $this->_usedProperties['urlType'] = true;
            $this->urlType = $config['urlType'];
            unset($config['urlType']);
        }

        if (array_key_exists('faxType', $config)) {
            $this->_usedProperties['faxType'] = true;
            $this->faxType = $config['faxType'];
            unset($config['faxType']);
        }

        if (array_key_exists('socialMediaProfileType', $config)) {
            $this->_usedProperties['socialMediaProfileType'] = true;
            $this->socialMediaProfileType = $config['socialMediaProfileType'];
            unset($config['socialMediaProfileType']);
        }

        if (array_key_exists('country', $config)) {
            $this->_usedProperties['country'] = true;
            $this->country = $config['country'];
            unset($config['country']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['phoneType'])) {
            $output['phoneType'] = $this->phoneType;
        }
        if (isset($this->_usedProperties['phoneTypeMobile'])) {
            $output['phoneTypeMobile'] = $this->phoneTypeMobile;
        }
        if (isset($this->_usedProperties['phoneTypeIsdn'])) {
            $output['phoneTypeIsdn'] = $this->phoneTypeIsdn;
        }
        if (isset($this->_usedProperties['emailType'])) {
            $output['emailType'] = $this->emailType;
        }
        if (isset($this->_usedProperties['addressType'])) {
            $output['addressType'] = $this->addressType;
        }
        if (isset($this->_usedProperties['urlType'])) {
            $output['urlType'] = $this->urlType;
        }
        if (isset($this->_usedProperties['faxType'])) {
            $output['faxType'] = $this->faxType;
        }
        if (isset($this->_usedProperties['socialMediaProfileType'])) {
            $output['socialMediaProfileType'] = $this->socialMediaProfileType;
        }
        if (isset($this->_usedProperties['country'])) {
            $output['country'] = $this->country;
        }

        return $output;
    }

}
