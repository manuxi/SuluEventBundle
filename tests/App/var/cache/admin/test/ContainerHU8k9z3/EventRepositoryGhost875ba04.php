<?php

namespace ContainerHU8k9z3;

class EventRepositoryGhost875ba04 extends \Manuxi\SuluEventBundle\Repository\EventRepository implements \Symfony\Component\VarExporter\LazyObjectInterface
{
    use \Symfony\Component\VarExporter\LazyGhostTrait;

    private const LAZY_OBJECT_PROPERTY_SCOPES = [
        "\0".'*'."\0".'_class' => [parent::class, '_class', null, 8],
        "\0".'*'."\0".'_em' => [parent::class, '_em', null, 8],
        "\0".'*'."\0".'_entityName' => [parent::class, '_entityName', null, 8],
        "\0".'Doctrine\\Bundle\\DoctrineBundle\\Repository\\LazyServiceEntityRepository'."\0".'entityClass' => ['Doctrine\\Bundle\\DoctrineBundle\\Repository\\LazyServiceEntityRepository', 'entityClass', null, 530],
        "\0".'Doctrine\\Bundle\\DoctrineBundle\\Repository\\LazyServiceEntityRepository'."\0".'registry' => ['Doctrine\\Bundle\\DoctrineBundle\\Repository\\LazyServiceEntityRepository', 'registry', null, 530],
        "\0".parent::class."\0".'dimensionContentQueryEnhancer' => [parent::class, 'dimensionContentQueryEnhancer', null, 16],
        '_class' => [parent::class, '_class', null, 8],
        '_em' => [parent::class, '_em', null, 8],
        '_entityName' => [parent::class, '_entityName', null, 8],
        'dimensionContentQueryEnhancer' => [parent::class, 'dimensionContentQueryEnhancer', null, 16],
        'entityClass' => ['Doctrine\\Bundle\\DoctrineBundle\\Repository\\LazyServiceEntityRepository', 'entityClass', null, 530],
        'registry' => ['Doctrine\\Bundle\\DoctrineBundle\\Repository\\LazyServiceEntityRepository', 'registry', null, 530],
    ];
}

// Help opcache.preload discover always-needed symbols
class_exists(\Symfony\Component\VarExporter\Internal\Hydrator::class);
class_exists(\Symfony\Component\VarExporter\Internal\LazyObjectRegistry::class);
class_exists(\Symfony\Component\VarExporter\Internal\LazyObjectState::class);

if (!\class_exists('EventRepositoryGhost875ba04', false)) {
    \class_alias(__NAMESPACE__.'\\EventRepositoryGhost875ba04', 'EventRepositoryGhost875ba04', false);
}
