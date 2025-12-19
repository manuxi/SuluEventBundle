<?php

namespace Symfony\Config\Framework\Notifier;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class AdminRecipientConfig 
{
    private $email;
    private $phone;
    private $_usedProperties = [];

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function email($value): static
    {
        $this->_usedProperties['email'] = true;
        $this->email = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function phone($value): static
    {
        $this->_usedProperties['phone'] = true;
        $this->phone = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('email', $config)) {
            $this->_usedProperties['email'] = true;
            $this->email = $config['email'];
            unset($config['email']);
        }

        if (array_key_exists('phone', $config)) {
            $this->_usedProperties['phone'] = true;
            $this->phone = $config['phone'];
            unset($config['phone']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['email'])) {
            $output['email'] = $this->email;
        }
        if (isset($this->_usedProperties['phone'])) {
            $output['phone'] = $this->phone;
        }

        return $output;
    }

}
