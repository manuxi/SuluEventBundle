<?php

namespace Symfony\Config\SuluSecurity\ResetPassword;

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class MailConfig 
{
    private $tokenSendLimit;
    private $sender;
    private $subject;
    private $template;
    private $translationDomain;
    private $_usedProperties = [];

    /**
     * @default 3
     * @param ParamConfigurator|int $value
     * @return $this
     */
    public function tokenSendLimit($value): static
    {
        $this->_usedProperties['tokenSendLimit'] = true;
        $this->tokenSendLimit = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function sender($value): static
    {
        $this->_usedProperties['sender'] = true;
        $this->sender = $value;

        return $this;
    }

    /**
     * @default 'sulu_security.reset_mail_subject'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function subject($value): static
    {
        $this->_usedProperties['subject'] = true;
        $this->subject = $value;

        return $this;
    }

    /**
     * @default '@SuluSecurity/mail_templates/reset_password.html.twig'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function template($value): static
    {
        $this->_usedProperties['template'] = true;
        $this->template = $value;

        return $this;
    }

    /**
     * @default 'admin'
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function translationDomain($value): static
    {
        $this->_usedProperties['translationDomain'] = true;
        $this->translationDomain = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('token_send_limit', $config)) {
            $this->_usedProperties['tokenSendLimit'] = true;
            $this->tokenSendLimit = $config['token_send_limit'];
            unset($config['token_send_limit']);
        }

        if (array_key_exists('sender', $config)) {
            $this->_usedProperties['sender'] = true;
            $this->sender = $config['sender'];
            unset($config['sender']);
        }

        if (array_key_exists('subject', $config)) {
            $this->_usedProperties['subject'] = true;
            $this->subject = $config['subject'];
            unset($config['subject']);
        }

        if (array_key_exists('template', $config)) {
            $this->_usedProperties['template'] = true;
            $this->template = $config['template'];
            unset($config['template']);
        }

        if (array_key_exists('translation_domain', $config)) {
            $this->_usedProperties['translationDomain'] = true;
            $this->translationDomain = $config['translation_domain'];
            unset($config['translation_domain']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['tokenSendLimit'])) {
            $output['token_send_limit'] = $this->tokenSendLimit;
        }
        if (isset($this->_usedProperties['sender'])) {
            $output['sender'] = $this->sender;
        }
        if (isset($this->_usedProperties['subject'])) {
            $output['subject'] = $this->subject;
        }
        if (isset($this->_usedProperties['template'])) {
            $output['template'] = $this->template;
        }
        if (isset($this->_usedProperties['translationDomain'])) {
            $output['translation_domain'] = $this->translationDomain;
        }

        return $output;
    }

}
