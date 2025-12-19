<?php

namespace Symfony\Config;

require_once __DIR__.\DIRECTORY_SEPARATOR.'SuluAdmin'.\DIRECTORY_SEPARATOR.'ResourcesConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'SuluAdmin'.\DIRECTORY_SEPARATOR.'CollaborationConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'SuluAdmin'.\DIRECTORY_SEPARATOR.'FormsConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'SuluAdmin'.\DIRECTORY_SEPARATOR.'TemplatesConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'SuluAdmin'.\DIRECTORY_SEPARATOR.'ListsConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'SuluAdmin'.\DIRECTORY_SEPARATOR.'FieldTypeOptionsConfig.php';

use Symfony\Component\Config\Loader\ParamConfigurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class SuluAdminConfig implements \Symfony\Component\Config\Builder\ConfigBuilderInterface
{
    private $name;
    private $email;
    private $userDataService;
    private $resources;
    private $collaboration;
    private $forms;
    private $templates;
    private $lists;
    private $iconSets;
    private $fieldTypeOptions;
    private $_usedProperties = [];
    private $_hasDeprecatedCalls = false;

    /**
     * @default 'Sulu Admin'
     * @param ParamConfigurator|mixed $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function name($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['name'] = true;
        $this->name = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|mixed $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function email($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['email'] = true;
        $this->email = $value;

        return $this;
    }

    /**
     * @default 'sulu_security.user_manager'
     * @param ParamConfigurator|mixed $value
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function userDataService($value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['userDataService'] = true;
        $this->userDataService = $value;

        return $this;
    }

    /**
     * @deprecated since Symfony 7.4
     */
    public function resources(string $resourceKey, array $value = []): \Symfony\Config\SuluAdmin\ResourcesConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (!isset($this->resources[$resourceKey])) {
            $this->_usedProperties['resources'] = true;
            $this->resources[$resourceKey] = new \Symfony\Config\SuluAdmin\ResourcesConfig($value);
        } elseif (1 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "resources()" has already been initialized. You cannot pass values the second time you call resources().');
        }

        return $this->resources[$resourceKey];
    }

    /**
     * @default {"enabled":false,"interval":20,"threshold":60}
     * @deprecated since Symfony 7.4
     */
    public function collaboration(array $value = []): \Symfony\Config\SuluAdmin\CollaborationConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (null === $this->collaboration) {
            $this->_usedProperties['collaboration'] = true;
            $this->collaboration = new \Symfony\Config\SuluAdmin\CollaborationConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "collaboration()" has already been initialized. You cannot pass values the second time you call collaboration().');
        }

        return $this->collaboration;
    }

    /**
     * @deprecated since Symfony 7.4
     */
    public function forms(array $value = []): \Symfony\Config\SuluAdmin\FormsConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (null === $this->forms) {
            $this->_usedProperties['forms'] = true;
            $this->forms = new \Symfony\Config\SuluAdmin\FormsConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "forms()" has already been initialized. You cannot pass values the second time you call forms().');
        }

        return $this->forms;
    }

    /**
     * @deprecated since Symfony 7.4
     */
    public function templates(string $name, array $value = []): \Symfony\Config\SuluAdmin\TemplatesConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (!isset($this->templates[$name])) {
            $this->_usedProperties['templates'] = true;
            $this->templates[$name] = new \Symfony\Config\SuluAdmin\TemplatesConfig($value);
        } elseif (1 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "templates()" has already been initialized. You cannot pass values the second time you call templates().');
        }

        return $this->templates[$name];
    }

    /**
     * @deprecated since Symfony 7.4
     */
    public function lists(array $value = []): \Symfony\Config\SuluAdmin\ListsConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (null === $this->lists) {
            $this->_usedProperties['lists'] = true;
            $this->lists = new \Symfony\Config\SuluAdmin\ListsConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "lists()" has already been initialized. You cannot pass values the second time you call lists().');
        }

        return $this->lists;
    }

    /**
     * @return $this
     * @deprecated since Symfony 7.4
     */
    public function iconSets(string $name, mixed $value): static
    {
        $this->_hasDeprecatedCalls = true;
        $this->_usedProperties['iconSets'] = true;
        $this->iconSets[$name] = $value;

        return $this;
    }

    /**
     * @deprecated since Symfony 7.4
     */
    public function fieldTypeOptions(array $value = []): \Symfony\Config\SuluAdmin\FieldTypeOptionsConfig
    {
        $this->_hasDeprecatedCalls = true;
        if (null === $this->fieldTypeOptions) {
            $this->_usedProperties['fieldTypeOptions'] = true;
            $this->fieldTypeOptions = new \Symfony\Config\SuluAdmin\FieldTypeOptionsConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "fieldTypeOptions()" has already been initialized. You cannot pass values the second time you call fieldTypeOptions().');
        }

        return $this->fieldTypeOptions;
    }

    public function getExtensionAlias(): string
    {
        return 'sulu_admin';
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('name', $config)) {
            $this->_usedProperties['name'] = true;
            $this->name = $config['name'];
            unset($config['name']);
        }

        if (array_key_exists('email', $config)) {
            $this->_usedProperties['email'] = true;
            $this->email = $config['email'];
            unset($config['email']);
        }

        if (array_key_exists('user_data_service', $config)) {
            $this->_usedProperties['userDataService'] = true;
            $this->userDataService = $config['user_data_service'];
            unset($config['user_data_service']);
        }

        if (array_key_exists('resources', $config)) {
            $this->_usedProperties['resources'] = true;
            $this->resources = array_map(fn ($v) => new \Symfony\Config\SuluAdmin\ResourcesConfig($v), $config['resources']);
            unset($config['resources']);
        }

        if (array_key_exists('collaboration', $config)) {
            $this->_usedProperties['collaboration'] = true;
            $this->collaboration = new \Symfony\Config\SuluAdmin\CollaborationConfig($config['collaboration']);
            unset($config['collaboration']);
        }

        if (array_key_exists('forms', $config)) {
            $this->_usedProperties['forms'] = true;
            $this->forms = new \Symfony\Config\SuluAdmin\FormsConfig($config['forms']);
            unset($config['forms']);
        }

        if (array_key_exists('templates', $config)) {
            $this->_usedProperties['templates'] = true;
            $this->templates = array_map(fn ($v) => new \Symfony\Config\SuluAdmin\TemplatesConfig($v), $config['templates']);
            unset($config['templates']);
        }

        if (array_key_exists('lists', $config)) {
            $this->_usedProperties['lists'] = true;
            $this->lists = new \Symfony\Config\SuluAdmin\ListsConfig($config['lists']);
            unset($config['lists']);
        }

        if (array_key_exists('icon_sets', $config)) {
            $this->_usedProperties['iconSets'] = true;
            $this->iconSets = $config['icon_sets'];
            unset($config['icon_sets']);
        }

        if (array_key_exists('field_type_options', $config)) {
            $this->_usedProperties['fieldTypeOptions'] = true;
            $this->fieldTypeOptions = new \Symfony\Config\SuluAdmin\FieldTypeOptionsConfig($config['field_type_options']);
            unset($config['field_type_options']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['name'])) {
            $output['name'] = $this->name;
        }
        if (isset($this->_usedProperties['email'])) {
            $output['email'] = $this->email;
        }
        if (isset($this->_usedProperties['userDataService'])) {
            $output['user_data_service'] = $this->userDataService;
        }
        if (isset($this->_usedProperties['resources'])) {
            $output['resources'] = array_map(fn ($v) => $v->toArray(), $this->resources);
        }
        if (isset($this->_usedProperties['collaboration'])) {
            $output['collaboration'] = $this->collaboration->toArray();
        }
        if (isset($this->_usedProperties['forms'])) {
            $output['forms'] = $this->forms->toArray();
        }
        if (isset($this->_usedProperties['templates'])) {
            $output['templates'] = array_map(fn ($v) => $v->toArray(), $this->templates);
        }
        if (isset($this->_usedProperties['lists'])) {
            $output['lists'] = $this->lists->toArray();
        }
        if (isset($this->_usedProperties['iconSets'])) {
            $output['icon_sets'] = $this->iconSets;
        }
        if (isset($this->_usedProperties['fieldTypeOptions'])) {
            $output['field_type_options'] = $this->fieldTypeOptions->toArray();
        }
        if ($this->_hasDeprecatedCalls) {
            trigger_deprecation('symfony/config', '7.4', 'Calling any fluent method on "%s" is deprecated; pass the configuration to the constructor instead.', $this::class);
        }

        return $output;
    }

}
