<?php

namespace ContainerHU8k9z3;

class MediaRepositoryGhost74a7cec extends \Sulu\Bundle\MediaBundle\Entity\MediaRepository implements \Symfony\Component\VarExporter\LazyObjectInterface
{
    use \Symfony\Component\VarExporter\LazyGhostTrait;

    private const LAZY_OBJECT_PROPERTY_SCOPES = [
        "\0".'*'."\0".'_class' => [parent::class, '_class', null, 8],
        "\0".'*'."\0".'_em' => [parent::class, '_em', null, 8],
        "\0".'*'."\0".'_entityName' => [parent::class, '_entityName', null, 8],
        "\0".parent::class."\0".'accessControlQueryEnhancer' => [parent::class, 'accessControlQueryEnhancer', null, 16],
        '_class' => [parent::class, '_class', null, 8],
        '_em' => [parent::class, '_em', null, 8],
        '_entityName' => [parent::class, '_entityName', null, 8],
        'accessControlQueryEnhancer' => [parent::class, 'accessControlQueryEnhancer', null, 16],
    ];
}

// Help opcache.preload discover always-needed symbols
class_exists(\Symfony\Component\VarExporter\Internal\Hydrator::class);
class_exists(\Symfony\Component\VarExporter\Internal\LazyObjectRegistry::class);
class_exists(\Symfony\Component\VarExporter\Internal\LazyObjectState::class);

if (!\class_exists('MediaRepositoryGhost74a7cec', false)) {
    \class_alias(__NAMESPACE__.'\\MediaRepositoryGhost74a7cec', 'MediaRepositoryGhost74a7cec', false);
}
