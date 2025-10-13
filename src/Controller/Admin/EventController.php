<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Controller\Admin;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\View\ViewHandlerInterface;
use Manuxi\SuluEventBundle\Common\DoctrineListRepresentationFactory;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\Models\EventExcerptModel;
use Manuxi\SuluEventBundle\Entity\Models\EventModel;
use Manuxi\SuluEventBundle\Entity\Models\EventSeoModel;
use Manuxi\SuluEventBundle\Search\Event\EventPublishedEvent;
use Manuxi\SuluEventBundle\Search\Event\EventRemovedEvent;
use Manuxi\SuluEventBundle\Search\Event\EventSavedEvent;
use Manuxi\SuluEventBundle\Search\Event\EventUnpublishedEvent;
use Sulu\Bundle\TrashBundle\Application\TrashManager\TrashManagerInterface;
use Sulu\Component\Rest\AbstractRestController;
use Sulu\Component\Rest\Exception\EntityNotFoundException;
use Sulu\Component\Rest\Exception\MissingParameterException;
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
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @RouteResource("event")
 */
class EventController extends AbstractRestController implements ClassResourceInterface, SecuredControllerInterface
{
    use RequestParametersTrait;

    public function __construct(
        private readonly EventModel $eventModel,
        private readonly EventSeoModel $eventSeoModel,
        private readonly EventExcerptModel $eventExcerptModel,
        private readonly DoctrineListRepresentationFactory $doctrineListRepresentationFactory,
        private readonly SecurityCheckerInterface $securityChecker,
        private readonly TrashManagerInterface $trashManager,
        ViewHandlerInterface $viewHandler,
        ?TokenStorageInterface $tokenStorage = null,
    ) {
        parent::__construct($viewHandler, $tokenStorage);
    }

    public function cgetAction(Request $request): Response
    {
        $locale = $request->query->get('locale');
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
        $entity = $this->eventModel->createEvent($request);
        return $this->handleView($this->view($entity, 201));
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

        try {
            switch ($action) {
                case 'publish':
                    $entity = $this->eventModel->publish($id, $request);
                    break;
                case 'draft':
                case 'unpublish':
                    $entity = $this->eventModel->unpublish($id, $request);
                    break;
                case 'copy':
                    $entity = $this->eventModel->copy($id, $request);
                    break;
                case 'copy-locale':
                    $locale = $this->getRequestParameter($request, 'locale', true);
                    $srcLocale = $this->getRequestParameter($request, 'src', false, $locale);
                    $destLocales = $this->getRequestParameter($request, 'dest', true);
                    $destLocales = explode(',', $destLocales);

                    foreach ($destLocales as $destLocale) {
                        $this->securityChecker->checkPermission(
                            new SecurityCondition($this->getSecurityContext(), $destLocale),
                            PermissionTypes::EDIT
                        );
                    }

                    $entity = $this->eventModel->copyLanguage($id, $request, $srcLocale, $destLocales);
                    break;
                default:
                    throw new BadRequestHttpException(sprintf('Unknown action "%s".', $action));
            }
        } catch (RestException $exc) {
            $view = $this->view($exc->toArray(), 400);

            return $this->handleView($view);
        }

        return $this->handleView($this->view($entity));
    }

    /**
     * @throws EntityNotFoundException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function putAction(int $id, Request $request): Response
    {
        try {
            $action = $this->getRequestParameter($request, 'action', true);
            try {
                $entity = match ($action) {
                    'publish' => $this->eventModel->publish($id, $request),
                    'draft', 'unpublish' => $this->eventModel->unpublish($id, $request),
                    default => throw new BadRequestHttpException(sprintf('Unknown action "%s".', $action)),
                };
            } catch (RestException $exc) {
                $view = $this->view($exc->toArray(), 400);
                return $this->handleView($view);
            }
        } catch(MissingParameterException $e) {
            $entity = $this->eventModel->updateEvent($id, $request);

            $this->eventSeoModel->updateEventSeo($entity->getEventSeo(), $request);
            $this->eventExcerptModel->updateEventExcerpt($entity->getEventExcerpt(), $request);
        }

        return $this->handleView($this->view($entity));
    }

    /**
     * @throws EntityNotFoundException
     */
    public function deleteAction(int $id, Request $request): Response
    {
        $entity = $this->eventModel->getEvent($id, $request);

        $this->trashManager->store(Event::RESOURCE_KEY, $entity);

        $this->eventModel->deleteEvent($entity);

        return $this->handleView($this->view(null, 204));
    }

    public function getSecurityContext(): string
    {
        return Event::SECURITY_CONTEXT;
    }
}
