<?php

namespace Symfony\Config\SuluContact;

require_once __DIR__.\DIRECTORY_SEPARATOR.'Objects'.\DIRECTORY_SEPARATOR.'ContactConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Objects'.\DIRECTORY_SEPARATOR.'AccountConfig.php';

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class ObjectsConfig 
{
    private $contact;
    private $account;
    private $_usedProperties = [];

    /**
     * @default {"model":"Sulu\\Bundle\\ContactBundle\\Entity\\Contact","repository":"Sulu\\Bundle\\ContactBundle\\Entity\\ContactRepository"}
     */
    public function contact(array $value = []): \Symfony\Config\SuluContact\Objects\ContactConfig
    {
        if (null === $this->contact) {
            $this->_usedProperties['contact'] = true;
            $this->contact = new \Symfony\Config\SuluContact\Objects\ContactConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "contact()" has already been initialized. You cannot pass values the second time you call contact().');
        }

        return $this->contact;
    }

    /**
     * @default {"model":"Sulu\\Bundle\\ContactBundle\\Entity\\Account","repository":"Sulu\\Bundle\\ContactBundle\\Entity\\AccountRepository"}
     */
    public function account(array $value = []): \Symfony\Config\SuluContact\Objects\AccountConfig
    {
        if (null === $this->account) {
            $this->_usedProperties['account'] = true;
            $this->account = new \Symfony\Config\SuluContact\Objects\AccountConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "account()" has already been initialized. You cannot pass values the second time you call account().');
        }

        return $this->account;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('contact', $config)) {
            $this->_usedProperties['contact'] = true;
            $this->contact = new \Symfony\Config\SuluContact\Objects\ContactConfig($config['contact']);
            unset($config['contact']);
        }

        if (array_key_exists('account', $config)) {
            $this->_usedProperties['account'] = true;
            $this->account = new \Symfony\Config\SuluContact\Objects\AccountConfig($config['account']);
            unset($config['account']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['contact'])) {
            $output['contact'] = $this->contact->toArray();
        }
        if (isset($this->_usedProperties['account'])) {
            $output['account'] = $this->account->toArray();
        }

        return $output;
    }

}
