<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\DependencyInjection\Compiler;

use Manuxi\SuluEventBundle\Entity\Event;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class TemplatePathCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('sulu_admin')) {
            return;
        }

        $definition = $container->getDefinition('sulu_admin');

        $templatePath = __DIR__ . '/../../Resources/config/templates';

        $definition->addMethodCall('addPath', [
            $templatePath,
            Event::TEMPLATE_TYPE
        ]);
    }
}