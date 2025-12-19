<?php

namespace Symfony\Config\SuluAudienceTargeting;

require_once __DIR__.\DIRECTORY_SEPARATOR.'Objects'.\DIRECTORY_SEPARATOR.'TargetGroupConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Objects'.\DIRECTORY_SEPARATOR.'TargetGroupConditionConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Objects'.\DIRECTORY_SEPARATOR.'TargetGroupRuleConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'Objects'.\DIRECTORY_SEPARATOR.'TargetGroupWebspaceConfig.php';

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This class is automatically generated to help in creating a config.
 */
class ObjectsConfig 
{
    private $targetGroup;
    private $targetGroupCondition;
    private $targetGroupRule;
    private $targetGroupWebspace;
    private $_usedProperties = [];

    /**
     * @default {"model":"Sulu\\Bundle\\AudienceTargetingBundle\\Entity\\TargetGroup","repository":"Sulu\\Bundle\\AudienceTargetingBundle\\Entity\\TargetGroupRepository"}
     */
    public function targetGroup(array $value = []): \Symfony\Config\SuluAudienceTargeting\Objects\TargetGroupConfig
    {
        if (null === $this->targetGroup) {
            $this->_usedProperties['targetGroup'] = true;
            $this->targetGroup = new \Symfony\Config\SuluAudienceTargeting\Objects\TargetGroupConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "targetGroup()" has already been initialized. You cannot pass values the second time you call targetGroup().');
        }

        return $this->targetGroup;
    }

    /**
     * @default {"model":"Sulu\\Bundle\\AudienceTargetingBundle\\Entity\\TargetGroupCondition","repository":"Sulu\\Bundle\\AudienceTargetingBundle\\Entity\\TargetGroupConditionRepository"}
     */
    public function targetGroupCondition(array $value = []): \Symfony\Config\SuluAudienceTargeting\Objects\TargetGroupConditionConfig
    {
        if (null === $this->targetGroupCondition) {
            $this->_usedProperties['targetGroupCondition'] = true;
            $this->targetGroupCondition = new \Symfony\Config\SuluAudienceTargeting\Objects\TargetGroupConditionConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "targetGroupCondition()" has already been initialized. You cannot pass values the second time you call targetGroupCondition().');
        }

        return $this->targetGroupCondition;
    }

    /**
     * @default {"model":"Sulu\\Bundle\\AudienceTargetingBundle\\Entity\\TargetGroupRule","repository":"Sulu\\Bundle\\AudienceTargetingBundle\\Entity\\TargetGroupRuleRepository"}
     */
    public function targetGroupRule(array $value = []): \Symfony\Config\SuluAudienceTargeting\Objects\TargetGroupRuleConfig
    {
        if (null === $this->targetGroupRule) {
            $this->_usedProperties['targetGroupRule'] = true;
            $this->targetGroupRule = new \Symfony\Config\SuluAudienceTargeting\Objects\TargetGroupRuleConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "targetGroupRule()" has already been initialized. You cannot pass values the second time you call targetGroupRule().');
        }

        return $this->targetGroupRule;
    }

    /**
     * @default {"model":"Sulu\\Bundle\\AudienceTargetingBundle\\Entity\\TargetGroupWebspace","repository":"Sulu\\Bundle\\AudienceTargetingBundle\\Entity\\TargetGroupWebspaceRepository"}
     */
    public function targetGroupWebspace(array $value = []): \Symfony\Config\SuluAudienceTargeting\Objects\TargetGroupWebspaceConfig
    {
        if (null === $this->targetGroupWebspace) {
            $this->_usedProperties['targetGroupWebspace'] = true;
            $this->targetGroupWebspace = new \Symfony\Config\SuluAudienceTargeting\Objects\TargetGroupWebspaceConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "targetGroupWebspace()" has already been initialized. You cannot pass values the second time you call targetGroupWebspace().');
        }

        return $this->targetGroupWebspace;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('target_group', $config)) {
            $this->_usedProperties['targetGroup'] = true;
            $this->targetGroup = new \Symfony\Config\SuluAudienceTargeting\Objects\TargetGroupConfig($config['target_group']);
            unset($config['target_group']);
        }

        if (array_key_exists('target_group_condition', $config)) {
            $this->_usedProperties['targetGroupCondition'] = true;
            $this->targetGroupCondition = new \Symfony\Config\SuluAudienceTargeting\Objects\TargetGroupConditionConfig($config['target_group_condition']);
            unset($config['target_group_condition']);
        }

        if (array_key_exists('target_group_rule', $config)) {
            $this->_usedProperties['targetGroupRule'] = true;
            $this->targetGroupRule = new \Symfony\Config\SuluAudienceTargeting\Objects\TargetGroupRuleConfig($config['target_group_rule']);
            unset($config['target_group_rule']);
        }

        if (array_key_exists('target_group_webspace', $config)) {
            $this->_usedProperties['targetGroupWebspace'] = true;
            $this->targetGroupWebspace = new \Symfony\Config\SuluAudienceTargeting\Objects\TargetGroupWebspaceConfig($config['target_group_webspace']);
            unset($config['target_group_webspace']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['targetGroup'])) {
            $output['target_group'] = $this->targetGroup->toArray();
        }
        if (isset($this->_usedProperties['targetGroupCondition'])) {
            $output['target_group_condition'] = $this->targetGroupCondition->toArray();
        }
        if (isset($this->_usedProperties['targetGroupRule'])) {
            $output['target_group_rule'] = $this->targetGroupRule->toArray();
        }
        if (isset($this->_usedProperties['targetGroupWebspace'])) {
            $output['target_group_webspace'] = $this->targetGroupWebspace->toArray();
        }

        return $output;
    }

}
