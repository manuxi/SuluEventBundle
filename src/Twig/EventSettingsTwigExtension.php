<?php

namespace Manuxi\SuluEventBundle\Twig;

use Doctrine\ORM\EntityManagerInterface;

use Manuxi\SuluEventBundle\Entity\EventSettings;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class EventSettingsTwigExtension extends AbstractExtension
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function getFunctions()
    {
        return [
            new TwigFunction('load_event_settings', [$this, 'loadEventSettings']),
        ];
    }

    public function loadEventSettings(): EventSettings
    {
        $applicationSettings = $this->entityManager->getRepository(EventSettings::class)->findOneBy([]) ?? null;

        return $applicationSettings ?: new EventSettings();
    }
}