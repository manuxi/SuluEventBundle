<?php

namespace ContainerHU8k9z3;

class XmlTemplateFormMetadataLoaderGhost140fc70 extends \Sulu\Bundle\AdminBundle\Metadata\FormMetadata\XmlTemplateFormMetadataLoader implements \Symfony\Component\VarExporter\LazyObjectInterface
{
    use \Symfony\Component\VarExporter\LazyGhostTrait;

    private const LAZY_OBJECT_PROPERTY_SCOPES = [
        "\0".parent::class."\0".'cacheDir' => [parent::class, 'cacheDir', null, 16],
        "\0".parent::class."\0".'debug' => [parent::class, 'debug', null, 16],
        "\0".parent::class."\0".'fieldMetadataValidator' => [parent::class, 'fieldMetadataValidator', null, 16],
        "\0".parent::class."\0".'templateDirectories' => [parent::class, 'templateDirectories', null, 16],
        "\0".parent::class."\0".'templateXmlLoader' => [parent::class, 'templateXmlLoader', null, 16],
        'cacheDir' => [parent::class, 'cacheDir', null, 16],
        'debug' => [parent::class, 'debug', null, 16],
        'fieldMetadataValidator' => [parent::class, 'fieldMetadataValidator', null, 16],
        'templateDirectories' => [parent::class, 'templateDirectories', null, 16],
        'templateXmlLoader' => [parent::class, 'templateXmlLoader', null, 16],
    ];
}

// Help opcache.preload discover always-needed symbols
class_exists(\Symfony\Component\VarExporter\Internal\Hydrator::class);
class_exists(\Symfony\Component\VarExporter\Internal\LazyObjectRegistry::class);
class_exists(\Symfony\Component\VarExporter\Internal\LazyObjectState::class);

if (!\class_exists('XmlTemplateFormMetadataLoaderGhost140fc70', false)) {
    \class_alias(__NAMESPACE__.'\\XmlTemplateFormMetadataLoaderGhost140fc70', 'XmlTemplateFormMetadataLoaderGhost140fc70', false);
}
