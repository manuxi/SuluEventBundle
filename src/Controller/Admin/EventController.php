<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Controller\Admin;

use Manuxi\SuluEventBundle\Common\DoctrineListRepresentationFactory;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\Models\EventExcerptModel;
use Manuxi\SuluEventBundle\Entity\Models\EventModel;
use Manuxi\SuluEventBundle\Entity\Models\EventSeoModel;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\View\ViewHandlerInterface;
//use HandcraftedInTheAlps\RestRoutingBundle\Controller\Annotations\RouteResource;
//use HandcraftedInTheAlps\RestRoutingBundle\Routing\ClassResourceInterface;
use Sulu\Bundle\RouteBundle\Entity\RouteRepositoryInterface;
use Sulu\Bundle\RouteBundle\Manager\RouteManagerInterface;
use Sulu\Bundle\TrashBundle\Application\TrashManager\TrashManagerInterface;
use Sulu\Component\Rest\AbstractRestController;
use Sulu\Component\Rest\Exception\EntityNotFoundException;
use Sulu\Component\Rest\Exception\RestException;
use Sulu\Component\Rest\RequestParametersTrait;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;
use Sulu\Component\Security\Authorization\SecurityCondition;
use Sulu\Component\Security\SecuredControllerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @RouteResource("event")
 */
class EventController extends AbstractRestController implements ClassResourceInterface, SecuredControllerInterface
{
    use RequestParametersTrait;

    public function __construct(
        private EventModel $eventModel,
        private EventSeoModel $eventSeoModel,
        private EventExcerptModel $eventExcerptModel,
        private RouteManagerInterface $routeManager,
        private RouteRepositoryInterface $routeRepository,
        private DoctrineListRepresentationFactory $doctrineListRepresentationFactory,
        private SecurityCheckerInterface $securityChecker,
        private TrashManagerInterface $trashManager,
        ViewHandlerInterface $viewHandler,
        ?TokenStorageInterface $tokenStorage = null
    ) {
        parent::__construct($viewHandler, $tokenStorage);
    }

    public function cgetAction(Request $request): Response
    {
        $locale             = $request->query->get('locale');
        $listRepresentation = $this->doctrineListRepresentationFactory->createDoctrineListRepresentation(
            Event::RESOURCE_KEY,
            [],
            ['locale' => $locale]
        );

        return $this->handleView($this->view($listRepresentation));

    }

    /**
     * @throws EntityNotFoundException
     */
    public function getAction(int $id, Request $request): Response
    {
        $event = $this->eventModel->getEvent($id, $request);
        return $this->handleView($this->view($event));
    }

    /**
     * @throws EntityNotFoundException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function postAction(Request $request): Response
    {
        $event = $this->eventModel->createEvent($request);
        $this->updateRoutesForEntity($event);

        return $this->handleView($this->view($event, 201));
    }

    /**
     * @Rest\Post("/events/{id}")
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws EntityNotFoundException
     */
    public function postTriggerAction(int $id, Request $request): Response
    {
        $action = $this->getRequestParameter($request, 'action', true);
        $locale = $this->getRequestParameter($request, 'locale', true);

        try {
            switch ($action) {
                case 'enable':
                    $event = $this->eventModel->enableEvent($id, $request);
                    break;
                case 'disable':
                    $event = $this->eventModel->disableEvent($id, $request);
                    break;
                case 'copy-locale':
                    $srcLocale = $this->getRequestParameter($request, 'src', false, $locale);
                    $destLocales = $this->getRequestParameter($request, 'dest', true);
                    $destLocales = explode(',', $destLocales);

                    foreach ($destLocales as $destLocale) {
                        $this->securityChecker->checkPermission(
                            new SecurityCondition($this->getSecurityContext(), $destLocale),
                            PermissionTypes::EDIT
                        );
                    }

                    $event = $this->eventModel->copyLanguage($id, $request, $srcLocale, $destLocales);
                    break;
                default:
                    throw new BadRequestHttpException(sprintf('Unknown action "%s".', $action));
            }
        } catch (RestException $exc) {
            $view = $this->view($exc->toArray(), 400);
            return $this->handleView($view);
        }

        return $this->handleView($this->view($event));
    }

    /**
     * @throws EntityNotFoundException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function putAction(int $id, Request $request): Response
    {
        $event = $this->eventModel->updateEvent($id, $request);
        $this->updateRoutesForEntity($event);

        $this->eventSeoModel->updateEventSeo($event->getEventSeo(), $request);
        $this->eventExcerptModel->updateEventExcerpt($event->getEventExcerpt(), $request);

        return $this->handleView($this->view($event));
    }

    /**
     * @param int $id
     * @param Request $request
     * @return Response
     * @throws EntityNotFoundException
     */
    public function deleteAction(int $id, Request $request): Response
    {
        $entity = $this->eventModel->getEvent($id, $request);
        $title = $entity->getTitle() ?? 'n.a.';

        $this->trashManager->store(Event::RESOURCE_KEY, $entity);

        $this->removeRoutesForEntity($entity);

        $this->eventModel->deleteEvent($id, $title);
        return $this->handleView($this->view(null, 204));
    }

    public function getSecurityContext(): string
    {
        return Event::SECURITY_CONTEXT;
    }

    protected function updateRoutesForEntity(Event $entity): void
    {
        $this->routeManager->createOrUpdateByAttributes(
            Event::class,
            (string) $entity->getId(),
            $entity->getLocale(),
            $entity->getRoutePath()
        );
    }

    protected function removeRoutesForEntity(Event $entity): void
    {
        $routes = $this->routeRepository->findAllByEntity(
            Event::class,
            (string) $entity->getId(),
            $entity->getLocale()
        );

        foreach ($routes as $route) {
            $this->routeRepository->remove($route);
        }
    }
}
