<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Controller\Admin;

use Manuxi\SuluEventBundle\Common\DoctrineListRepresentationFactory;
use Manuxi\SuluEventBundle\Entity\Location;
use Manuxi\SuluEventBundle\Entity\Models\LocationModel;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\View\ViewHandlerInterface;
use Sulu\Component\Rest\AbstractRestController;
use Sulu\Component\Rest\Exception\EntityNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @RouteResource("location")
 */
class LocationController extends AbstractRestController implements ClassResourceInterface
{
    private LocationModel $locationModel;
    private DoctrineListRepresentationFactory $doctrineListRepresentationFactory;

    public function __construct(
        LocationModel $locationModel,
        DoctrineListRepresentationFactory $doctrineListRepresentationFactory,
        ViewHandlerInterface $viewHandler,
        ?TokenStorageInterface $tokenStorage = null
    ) {
        parent::__construct($viewHandler, $tokenStorage);
        $this->locationModel = $locationModel;
        $this->doctrineListRepresentationFactory = $doctrineListRepresentationFactory;
    }

    public function cgetAction(): Response
    {
        $listRepresentation = $this->doctrineListRepresentationFactory->createDoctrineListRepresentation(
            Location::RESOURCE_KEY
        );

        return $this->handleView($this->view($listRepresentation));
    }

    /**
     * @param int $id
     * @param Request $request
     * @return Response
     * @throws EntityNotFoundException
     * @noinspection PhpUnusedParameterInspection
     */
    public function getAction(int $id, Request $request): Response
    {
        $entity = $this->locationModel->getLocation($id);
        return $this->handleView($this->view($entity));
    }

    /**
     * @param Request $request
     * @return Response
     * @throws EntityNotFoundException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function postAction(Request $request): Response
    {
        $entity = $this->locationModel->createLocation($request);
        return $this->handleView($this->view($entity));
    }

    /**
     * @param int $id
     * @param Request $request
     * @return Response
     * @throws EntityNotFoundException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function putAction(int $id, Request $request): Response
    {
        $entity = $this->locationModel->updateLocation($id, $request);
        return $this->handleView($this->view($entity));
    }

    /**
     * @param int $id
     * @return Response
     * @throws EntityNotFoundException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function deleteAction(int $id): Response
    {
        $location = $this->locationModel->getLocation($id);
        $title = $location->getName() ?? 'n.a.';
        $this->locationModel->deleteLocation($id, $title);
        return $this->handleView($this->view(null, 204));
    }

}
