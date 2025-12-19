<?php

namespace Symfony\Config\Security\FirewallConfig\RememberMe;

require_once __DIR__.\DIRECTORY_SEPARATOR.'TokenProvider'.\DIRECTORY_SEPARATOR.'DoctrineConfig.php';

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class TokenProviderConfig 
{
    private $service;
    private $doctrine;
    private $_usedProperties = [];

    /**
     * The service ID of a custom remember-me token provider.
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function service($value): static
    {
        $this->_usedProperties['service'] = true;
        $this->service = $value;

        return $this;
    }

    /**
     * @template TValue of array|bool
     * @param TValue $value
     * @default {"enabled":false,"connection":null}
     * @return \Symfony\Config\Security\FirewallConfig\RememberMe\TokenProvider\DoctrineConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\Security\FirewallConfig\RememberMe\TokenProvider\DoctrineConfig : static)
     */
    public function doctrine(array|bool $value = []): \Symfony\Config\Security\FirewallConfig\RememberMe\TokenProvider\DoctrineConfig|static
    {
        if (!\is_array($value)) {
            $this->_usedProperties['doctrine'] = true;
            $this->doctrine = $value;

            return $this;
        }

        if (!$this->doctrine instanceof \Symfony\Config\Security\FirewallConfig\RememberMe\TokenProvider\DoctrineConfig) {
            $this->_usedProperties['doctrine'] = true;
            $this->doctrine = new \Symfony\Config\Security\FirewallConfig\RememberMe\TokenProvider\DoctrineConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "doctrine()" has already been initialized. You cannot pass values the second time you call doctrine().');
        }

        return $this->doctrine;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('service', $config)) {
            $this->_usedProperties['service'] = true;
            $this->service = $config['service'];
            unset($config['service']);
        }

        if (array_key_exists('doctrine', $config)) {
            $this->_usedProperties['doctrine'] = true;
            $this->doctrine = \is_array($config['doctrine']) ? new \Symfony\Config\Security\FirewallConfig\RememberMe\TokenProvider\DoctrineConfig($config['doctrine']) : $config['doctrine'];
            unset($config['doctrine']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['service'])) {
            $output['service'] = $this->service;
        }
        if (isset($this->_usedProperties['doctrine'])) {
            $output['doctrine'] = $this->doctrine instanceof \Symfony\Config\Security\FirewallConfig\RememberMe\TokenProvider\DoctrineConfig ? $this->doctrine->toArray() : $this->doctrine;
        }

        return $output;
    }

}
