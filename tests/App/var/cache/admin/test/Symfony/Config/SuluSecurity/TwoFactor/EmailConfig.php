<?php

namespace Symfony\Config\SuluSecurity\TwoFactor;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class EmailConfig 
{
    private $template;
    private $_usedProperties = [];

    /**
     * @default '@SuluSecurity/mail_templates/two_factor'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function template($value): static
    {
        $this->_usedProperties['template'] = true;
        $this->template = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('template', $config)) {
            $this->_usedProperties['template'] = true;
            $this->template = $config['template'];
            unset($config['template']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['template'])) {
            $output['template'] = $this->template;
        }

        return $output;
    }

}
