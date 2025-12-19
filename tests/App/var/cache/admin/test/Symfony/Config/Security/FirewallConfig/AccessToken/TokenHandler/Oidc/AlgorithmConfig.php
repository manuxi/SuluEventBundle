<?php

namespace Symfony\Config\Security\FirewallConfig\AccessToken\TokenHandler\Oidc;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class AlgorithmConfig 
{

    public function __construct(array $config = [])
    {
        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];

        return $output;
    }

}
