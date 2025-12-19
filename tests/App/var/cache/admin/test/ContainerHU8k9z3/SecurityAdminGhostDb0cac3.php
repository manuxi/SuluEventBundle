<?php

namespace ContainerHU8k9z3;

class SecurityAdminGhostDb0cac3 extends \Sulu\Bundle\SecurityBundle\Admin\SecurityAdmin implements \Symfony\Component\VarExporter\LazyObjectInterface
{
    use \Symfony\Component\VarExporter\LazyGhostTrait;

    private const LAZY_OBJECT_PROPERTY_SCOPES = [
        "\0".parent::class."\0".'adminPool' => [parent::class, 'adminPool', null, 16],
        "\0".parent::class."\0".'resources' => [parent::class, 'resources', null, 16],
        "\0".parent::class."\0".'securityChecker' => [parent::class, 'securityChecker', null, 16],
        "\0".parent::class."\0".'translator' => [parent::class, 'translator', null, 16],
        "\0".parent::class."\0".'viewBuilderFactory' => [parent::class, 'viewBuilderFactory', null, 16],
        'adminPool' => [parent::class, 'adminPool', null, 16],
        'resources' => [parent::class, 'resources', null, 16],
        'securityChecker' => [parent::class, 'securityChecker', null, 16],
        'translator' => [parent::class, 'translator', null, 16],
        'viewBuilderFactory' => [parent::class, 'viewBuilderFactory', null, 16],
    ];
}

// Help opcache.preload discover always-needed symbols
class_exists(\Symfony\Component\VarExporter\Internal\Hydrator::class);
class_exists(\Symfony\Component\VarExporter\Internal\LazyObjectRegistry::class);
class_exists(\Symfony\Component\VarExporter\Internal\LazyObjectState::class);

if (!\class_exists('SecurityAdminGhostDb0cac3', false)) {
    \class_alias(__NAMESPACE__.'\\SecurityAdminGhostDb0cac3', 'SecurityAdminGhostDb0cac3', false);
}
