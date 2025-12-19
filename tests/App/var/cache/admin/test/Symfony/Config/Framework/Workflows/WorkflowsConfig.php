<?php

namespace Symfony\Config\Framework\Workflows;

require_once __DIR__.\DIRECTORY_SEPARATOR.'WorkflowsConfig'.\DIRECTORY_SEPARATOR.'AuditTrailConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'WorkflowsConfig'.\DIRECTORY_SEPARATOR.'MarkingStoreConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'WorkflowsConfig'.\DIRECTORY_SEPARATOR.'PlaceConfig.php';
require_once __DIR__.\DIRECTORY_SEPARATOR.'WorkflowsConfig'.\DIRECTORY_SEPARATOR.'TransitionConfig.php';

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Loader\ParamConfigurator;

/**
 * This class is automatically generated to help in creating a config.
 */
class WorkflowsConfig 
{
    private $auditTrail;
    private $type;
    private $markingStore;
    private $supports;
    private $definitionValidators;
    private $supportStrategy;
    private $initialMarking;
    private $eventsToDispatch;
    private $places;
    private $transitions;
    private $metadata;
    private $_usedProperties = [];

    /**
     * @template TValue of array|bool
     * @param TValue $value
     * @default {"enabled":false}
     * @return \Symfony\Config\Framework\Workflows\WorkflowsConfig\AuditTrailConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\Framework\Workflows\WorkflowsConfig\AuditTrailConfig : static)
     */
    public function auditTrail(array|bool $value = []): \Symfony\Config\Framework\Workflows\WorkflowsConfig\AuditTrailConfig|static
    {
        if (!\is_array($value)) {
            $this->_usedProperties['auditTrail'] = true;
            $this->auditTrail = $value;

            return $this;
        }

        if (!$this->auditTrail instanceof \Symfony\Config\Framework\Workflows\WorkflowsConfig\AuditTrailConfig) {
            $this->_usedProperties['auditTrail'] = true;
            $this->auditTrail = new \Symfony\Config\Framework\Workflows\WorkflowsConfig\AuditTrailConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "auditTrail()" has already been initialized. You cannot pass values the second time you call auditTrail().');
        }

        return $this->auditTrail;
    }

    /**
     * @default 'state_machine'
     * @param ParamConfigurator|'workflow'|'state_machine' $value
     * @return $this
     */
    public function type($value): static
    {
        $this->_usedProperties['type'] = true;
        $this->type = $value;

        return $this;
    }

    public function markingStore(array $value = []): \Symfony\Config\Framework\Workflows\WorkflowsConfig\MarkingStoreConfig
    {
        if (null === $this->markingStore) {
            $this->_usedProperties['markingStore'] = true;
            $this->markingStore = new \Symfony\Config\Framework\Workflows\WorkflowsConfig\MarkingStoreConfig($value);
        } elseif (0 < \func_num_args()) {
            throw new InvalidConfigurationException('The node created by "markingStore()" has already been initialized. You cannot pass values the second time you call markingStore().');
        }

        return $this->markingStore;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed>|mixed $value
     *
     * @return $this
     */
    public function supports(mixed $value): static
    {
        $this->_usedProperties['supports'] = true;
        $this->supports = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed> $value
     *
     * @return $this
     */
    public function definitionValidators(ParamConfigurator|array $value): static
    {
        $this->_usedProperties['definitionValidators'] = true;
        $this->definitionValidators = $value;

        return $this;
    }

    /**
     * @default null
     * @param ParamConfigurator|mixed $value
     * @return $this
     */
    public function supportStrategy($value): static
    {
        $this->_usedProperties['supportStrategy'] = true;
        $this->supportStrategy = $value;

        return $this;
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed>|mixed $value
     *
     * @return $this
     */
    public function initialMarking(mixed $value): static
    {
        $this->_usedProperties['initialMarking'] = true;
        $this->initialMarking = $value;

        return $this;
    }

    /**
     * Select which Transition events should be dispatched for this Workflow.
     * @example workflow.enter
     * @example workflow.transition
     * @default null
     * @param ParamConfigurator|mixed $value
     *
     * @return $this
     */
    public function eventsToDispatch(mixed $value = NULL): static
    {
        $this->_usedProperties['eventsToDispatch'] = true;
        $this->eventsToDispatch = $value;

        return $this;
    }

    /**
     * @template TValue of mixed
     * @param TValue $value
     * @return \Symfony\Config\Framework\Workflows\WorkflowsConfig\PlaceConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\Framework\Workflows\WorkflowsConfig\PlaceConfig : static)
     */
    public function place(mixed $value = []): \Symfony\Config\Framework\Workflows\WorkflowsConfig\PlaceConfig|static
    {
        $this->_usedProperties['places'] = true;
        if (!\is_array($value)) {
            $this->places[] = $value;

            return $this;
        }

        return $this->places[] = new \Symfony\Config\Framework\Workflows\WorkflowsConfig\PlaceConfig($value);
    }

    /**
     * @template TValue of mixed
     * @param TValue $value
     * @return \Symfony\Config\Framework\Workflows\WorkflowsConfig\TransitionConfig|$this
     * @psalm-return (TValue is array ? \Symfony\Config\Framework\Workflows\WorkflowsConfig\TransitionConfig : static)
     */
    public function transition(mixed $value = []): \Symfony\Config\Framework\Workflows\WorkflowsConfig\TransitionConfig|static
    {
        $this->_usedProperties['transitions'] = true;
        if (!\is_array($value)) {
            $this->transitions[] = $value;

            return $this;
        }

        return $this->transitions[] = new \Symfony\Config\Framework\Workflows\WorkflowsConfig\TransitionConfig($value);
    }

    /**
     * @param ParamConfigurator|list<ParamConfigurator|mixed> $value
     *
     * @return $this
     */
    public function metadata(ParamConfigurator|array $value): static
    {
        $this->_usedProperties['metadata'] = true;
        $this->metadata = $value;

        return $this;
    }

    public function __construct(array $config = [])
    {
        if (array_key_exists('audit_trail', $config)) {
            $this->_usedProperties['auditTrail'] = true;
            $this->auditTrail = \is_array($config['audit_trail']) ? new \Symfony\Config\Framework\Workflows\WorkflowsConfig\AuditTrailConfig($config['audit_trail']) : $config['audit_trail'];
            unset($config['audit_trail']);
        }

        if (array_key_exists('type', $config)) {
            $this->_usedProperties['type'] = true;
            $this->type = $config['type'];
            unset($config['type']);
        }

        if (array_key_exists('marking_store', $config)) {
            $this->_usedProperties['markingStore'] = true;
            $this->markingStore = new \Symfony\Config\Framework\Workflows\WorkflowsConfig\MarkingStoreConfig($config['marking_store']);
            unset($config['marking_store']);
        }

        if (array_key_exists('supports', $config)) {
            $this->_usedProperties['supports'] = true;
            $this->supports = $config['supports'];
            unset($config['supports']);
        }

        if (array_key_exists('definition_validators', $config)) {
            $this->_usedProperties['definitionValidators'] = true;
            $this->definitionValidators = $config['definition_validators'];
            unset($config['definition_validators']);
        }

        if (array_key_exists('support_strategy', $config)) {
            $this->_usedProperties['supportStrategy'] = true;
            $this->supportStrategy = $config['support_strategy'];
            unset($config['support_strategy']);
        }

        if (array_key_exists('initial_marking', $config)) {
            $this->_usedProperties['initialMarking'] = true;
            $this->initialMarking = $config['initial_marking'];
            unset($config['initial_marking']);
        }

        if (array_key_exists('events_to_dispatch', $config)) {
            $this->_usedProperties['eventsToDispatch'] = true;
            $this->eventsToDispatch = $config['events_to_dispatch'];
            unset($config['events_to_dispatch']);
        }

        if (array_key_exists('places', $config)) {
            $this->_usedProperties['places'] = true;
            $this->places = array_map(fn ($v) => \is_array($v) ? new \Symfony\Config\Framework\Workflows\WorkflowsConfig\PlaceConfig($v) : $v, $config['places']);
            unset($config['places']);
        }

        if (array_key_exists('transitions', $config)) {
            $this->_usedProperties['transitions'] = true;
            $this->transitions = array_map(fn ($v) => \is_array($v) ? new \Symfony\Config\Framework\Workflows\WorkflowsConfig\TransitionConfig($v) : $v, $config['transitions']);
            unset($config['transitions']);
        }

        if (array_key_exists('metadata', $config)) {
            $this->_usedProperties['metadata'] = true;
            $this->metadata = $config['metadata'];
            unset($config['metadata']);
        }

        if ($config) {
            throw new InvalidConfigurationException(sprintf('The following keys are not supported by "%s": ', __CLASS__).implode(', ', array_keys($config)));
        }
    }

    public function toArray(): array
    {
        $output = [];
        if (isset($this->_usedProperties['auditTrail'])) {
            $output['audit_trail'] = $this->auditTrail instanceof \Symfony\Config\Framework\Workflows\WorkflowsConfig\AuditTrailConfig ? $this->auditTrail->toArray() : $this->auditTrail;
        }
        if (isset($this->_usedProperties['type'])) {
            $output['type'] = $this->type;
        }
        if (isset($this->_usedProperties['markingStore'])) {
            $output['marking_store'] = $this->markingStore->toArray();
        }
        if (isset($this->_usedProperties['supports'])) {
            $output['supports'] = $this->supports;
        }
        if (isset($this->_usedProperties['definitionValidators'])) {
            $output['definition_validators'] = $this->definitionValidators;
        }
        if (isset($this->_usedProperties['supportStrategy'])) {
            $output['support_strategy'] = $this->supportStrategy;
        }
        if (isset($this->_usedProperties['initialMarking'])) {
            $output['initial_marking'] = $this->initialMarking;
        }
        if (isset($this->_usedProperties['eventsToDispatch'])) {
            $output['events_to_dispatch'] = $this->eventsToDispatch;
        }
        if (isset($this->_usedProperties['places'])) {
            $output['places'] = array_map(fn ($v) => $v instanceof \Symfony\Config\Framework\Workflows\WorkflowsConfig\PlaceConfig ? $v->toArray() : $v, $this->places);
        }
        if (isset($this->_usedProperties['transitions'])) {
            $output['transitions'] = array_map(fn ($v) => $v instanceof \Symfony\Config\Framework\Workflows\WorkflowsConfig\TransitionConfig ? $v->toArray() : $v, $this->transitions);
        }
        if (isset($this->_usedProperties['metadata'])) {
            $output['metadata'] = $this->metadata;
        }

        return $output;
    }

}
