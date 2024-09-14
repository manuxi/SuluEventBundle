<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\View\ViewHandlerInterface;
use HandcraftedInTheAlps\RestRoutingBundle\Controller\Annotations\RouteResource;
use HandcraftedInTheAlps\RestRoutingBundle\Routing\ClassResourceInterface;
use Manuxi\SuluEventBundle\Domain\Event\Config\ModifiedEvent;
use Manuxi\SuluEventBundle\Entity\EventSettings;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Component\Rest\AbstractRestController;
use Sulu\Component\Security\SecuredControllerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;


/**
 * @RouteResource("event-settings")
 */
class SettingsController extends AbstractRestController implements ClassResourceInterface, SecuredControllerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private DomainEventCollectorInterface $domainEventCollector,
        ViewHandlerInterface $viewHandler,
        ?TokenStorageInterface $tokenStorage = null
    ) {
        parent::__construct($viewHandler, $tokenStorage);
    }

    public function getAction(): Response
    {
        $entity = $this->entityManager->getRepository(EventSettings::class)->findOneBy([]);

        return $this->handleView($this->view($this->getDataForEntity($entity ?: new EventSettings())));
    }

    public function putAction(Request $request): Response
    {
        $entity = $this->entityManager->getRepository(EventSettings::class)->findOneBy([]);
        if (!$entity) {
            $entity = new EventSettings();
            $this->entityManager->persist($entity);
        }

        $this->domainEventCollector->collect(
            new ModifiedEvent($entity, $request->request->all())
        );

        $data = $request->toArray();
        $this->mapDataToEntity($data, $entity);
        $this->entityManager->flush();

        return $this->handleView($this->view($this->getDataForEntity($entity)));
    }

    protected function getDataForEntity(EventSettings $entity): array
    {
        return [
            'toggleHeader' => $entity->getToggleHeader(),
            'toggleHero' => $entity->getToggleHero(),
            'toggleBreadcrumbs' => $entity->getToggleBreadcrumbs(),
            'pageEvents' => $entity->getPageEvents(),
            'pageEventsPending' => $entity->getPageEventsPending(),
            'pageEventsExpired' => $entity->getPageEventsExpired(),
        ];
    }

    protected function mapDataToEntity(array $data, EventSettings $entity): void
    {
        $entity->setToggleHeader($data['toggleHeader']);
        $entity->setToggleHero($data['toggleHero']);
        $entity->setToggleBreadcrumbs($data['toggleBreadcrumbs']);
        $entity->setPageEvents($data['pageEvents']);
        $entity->setPageEventsPending($data['pageEventsPending']);
        $entity->setPageEventsExpired($data['pageEventsExpired']);
    }

    public function getSecurityContext(): string
    {
        return EventSettings::SECURITY_CONTEXT;
    }

    public function getLocale(Request $request): ?string
    {
        return $request->query->get('locale');
    }
}