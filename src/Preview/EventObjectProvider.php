<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Preview;

use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Repository\EventRepository;
use Sulu\Bundle\PreviewBundle\Preview\PreviewContext;
use Sulu\Bundle\PreviewBundle\Preview\Provider\PreviewDefaultsProviderInterface;
use Sulu\Content\Application\ContentAggregator\ContentAggregatorInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;

class EventObjectProvider implements PreviewDefaultsProviderInterface
{
    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly ContentAggregatorInterface $contentAggregator,
    ) {
    }

    public function getDefaults(PreviewContext $previewContext): array
    {
        $event = $this->eventRepository->findById((int) $previewContext->getId());

        if (!$event) {
            return [];
        }

        // Resolve the DimensionContent for the requested locale
        $dimensionContent = $this->contentAggregator->aggregate(
            $event,
            [
                'locale' => $previewContext->getLocale(),
                'stage' => DimensionContentInterface::STAGE_DRAFT,
            ]
        );

        if (!$dimensionContent) {
            return [];
        }

        return [
            '_controller' => 'Manuxi\SuluEventBundle\Controller\Website\EventController::indexAction',
            'event' => $event,
            'dimensionContent' => $dimensionContent,
        ];
    }

    public function updateValues(PreviewContext $previewContext, array $defaults, array $data): array
    {
        // Update dimension content with preview data
        $dimensionContent = $defaults['dimensionContent'] ?? null;

        if ($dimensionContent) {
            if (isset($data['title'])) {
                $dimensionContent->setTitle($data['title']);
            }
            if (isset($data['subtitle'])) {
                $dimensionContent->setSubtitle($data['subtitle']);
            }
            if (isset($data['summary'])) {
                $dimensionContent->setSummary($data['summary']);
            }
            if (isset($data['text'])) {
                $dimensionContent->setText($data['text']);
            }
            if (isset($data['footer'])) {
                $dimensionContent->setFooter($data['footer']);
            }
        }

        return $defaults;
    }

    public function updateContext(PreviewContext $previewContext, array $defaults, array $context): array
    {
        $dimensionContent = $defaults['dimensionContent'] ?? null;

        if ($dimensionContent && \array_key_exists('template', $context)) {
            $dimensionContent->setTemplateKey($context['template']);
        }

        return $defaults;
    }

    public function getSecurityContext(PreviewContext $previewContext): ?string
    {
        return Event::SECURITY_CONTEXT;
    }
}