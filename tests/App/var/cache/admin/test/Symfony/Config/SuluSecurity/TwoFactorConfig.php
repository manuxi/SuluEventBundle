<?php

namespace Symfony\Config\SuluSecurity;

require_once __DIR__.\DIRECTORY_SEPARATOR.'TwoFactor'.\DIRECTORY_SEPARATOR.'EmailConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'TwoFactor'.\DIRECTORY_SEPARATOR.'ForceConfig.php';

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class TwoFactorConfig 
{
    private $email;
    private $force;
    private $_usedProperties = [];

    /**
     * @default {"template":"@SuluSecurity\/mail_templates\/two_factor"}
     */
    public function email(array $value = []): \Symfony\Config\SuluSecurity\TwoFactor\EmailConfig
    {
        if (null === $this->email) {
            $this->_usedProperties['email'] = true;
            $this->email = new \Symfony\Config\SuluSecurity\TwoFactor\EmailConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "email()" has already been initialized. You cannot pass values the second time you call email().');
        }

        return $this->email;
    }

    /**
     * @template TValue of array|bool
     * @param TValue $value
     * @default {"enabled":false,"pattern":"(.+)"}
     * @return \Symfony\Config\SuluSecurity\TwoFactor\ForceConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\SuluSecurity\TwoFactor\ForceConfig : static)
     */
    public function force(array|bool $value = []): \Symfony\Config\SuluSecurity\TwoFactor\ForceConfig|static
    {
        if (!\is_array($value)) {
            $this->_usedProperties['force'] = true;
            $this->force = $value;

            return $this;
        }

        if (!$this->force instanceof \Symfony\Config\SuluSecurity\TwoFactor\ForceConfig) {
            $this->_usedProperties['force'] = true;
            $this->force = new \Symfony\Config\SuluSecurity\TwoFactor\ForceConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "force()" has already been initialized. You cannot pass values the second time you call force().');
        }

        return $this->force;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('email', $config)) {
            $this->_usedProperties['email'] = true;
            $this->email = new \Symfony\Config\SuluSecurity\TwoFactor\EmailConfig($config['email']);
            unset($config['email']);
        }

        if (array_key_exists('force', $config)) {
            $this->_usedProperties['force'] = true;
            $this->force = \is_array($config['force']) ? new \Symfony\Config\SuluSecurity\TwoFactor\ForceConfig($config['force']) : $config['force'];
            unset($config['force']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['email'])) {
            $output['email'] = $this->email->toArray();
        }
        if (isset($this->_usedProperties['force'])) {
            $output['force'] = $this->force instanceof \Symfony\Config\SuluSecurity\TwoFactor\ForceConfig ? $this->force->toArray() : $this->force;
        }

        return $output;
    }

}
