<?php

namespace Symfony\Config\Framework;

require_once __DIR__.\DIRECTORY_SEPARATOR.'Notifier'.\DIRECTORY_SEPARATOR.'AdminRecipientConfig.php';

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class NotifierConfig 
{
    private $enabled;
    private $messageBus;
    private $chatterTransports;
    private $texterTransports;
    private $notificationOnFailedMessages;
    private $channelPolicy;
    private $adminRecipients;
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
     * The message bus to use. Defaults to the default bus if the Messenger component is installed.
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function messageBus($value): static
    {
        $this->_usedProperties['messageBus'] = true;
        $this->messageBus = $value;

        return $this;
    }

    /**
     * @return $this
     */
    public function chatterTransport(string $name, mixed $value): static
    {
        $this->_usedProperties['chatterTransports'] = true;
        $this->chatterTransports[$name] = $value;

        return $this;
    }

    /**
     * @return $this
     */
    public function texterTransport(string $name, mixed $value): static
    {
        $this->_usedProperties['texterTransports'] = true;
        $this->texterTransports[$name] = $value;

        return $this;
    }

    /**
     * @default false
     * @param ParamConfigurator|bool $value
     * @return $this
     */
    public function notificationOnFailedMessages($value): static
    {
        $this->_usedProperties['notificationOnFailedMessages'] = true;
        $this->notificationOnFailedMessages = $value;

        return $this;
    }

    /**
     * @return $this
     */
    public function channelPolicy(string $name, mixed $value): static
    {
        $this->_usedProperties['channelPolicy'] = true;
        $this->channelPolicy[$name] = $value;

        return $this;
    }

    public function adminRecipient(array $value = []): \Symfony\Config\Framework\Notifier\AdminRecipientConfig
    {
        $this->_usedProperties['adminRecipients'] = true;

        return $this->adminRecipients[] = new \Symfony\Config\Framework\Notifier\AdminRecipientConfig($value);
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('enabled', $config)) {
            $this->_usedProperties['enabled'] = true;
            $this->enabled = $config['enabled'];
            unset($config['enabled']);
        }

        if (array_key_exists('message_bus', $config)) {
            $this->_usedProperties['messageBus'] = true;
            $this->messageBus = $config['message_bus'];
            unset($config['message_bus']);
        }

        if (array_key_exists('chatter_transports', $config)) {
            $this->_usedProperties['chatterTransports'] = true;
            $this->chatterTransports = $config['chatter_transports'];
            unset($config['chatter_transports']);
        }

        if (array_key_exists('texter_transports', $config)) {
            $this->_usedProperties['texterTransports'] = true;
            $this->texterTransports = $config['texter_transports'];
            unset($config['texter_transports']);
        }

        if (array_key_exists('notification_on_failed_messages', $config)) {
            $this->_usedProperties['notificationOnFailedMessages'] = true;
            $this->notificationOnFailedMessages = $config['notification_on_failed_messages'];
            unset($config['notification_on_failed_messages']);
        }

        if (array_key_exists('channel_policy', $config)) {
            $this->_usedProperties['channelPolicy'] = true;
            $this->channelPolicy = $config['channel_policy'];
            unset($config['channel_policy']);
        }

        if (array_key_exists('admin_recipients', $config)) {
            $this->_usedProperties['adminRecipients'] = true;
            $this->adminRecipients = array_map(fn ($v) => new \Symfony\Config\Framework\Notifier\AdminRecipientConfig($v), $config['admin_recipients']);
            unset($config['admin_recipients']);
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
        if (isset($this->_usedProperties['messageBus'])) {
            $output['message_bus'] = $this->messageBus;
        }
        if (isset($this->_usedProperties['chatterTransports'])) {
            $output['chatter_transports'] = $this->chatterTransports;
        }
        if (isset($this->_usedProperties['texterTransports'])) {
            $output['texter_transports'] = $this->texterTransports;
        }
        if (isset($this->_usedProperties['notificationOnFailedMessages'])) {
            $output['notification_on_failed_messages'] = $this->notificationOnFailedMessages;
        }
        if (isset($this->_usedProperties['channelPolicy'])) {
            $output['channel_policy'] = $this->channelPolicy;
        }
        if (isset($this->_usedProperties['adminRecipients'])) {
            $output['admin_recipients'] = array_map(fn ($v) => $v->toArray(), $this->adminRecipients);
        }

        return $output;
    }

}
