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
    private $doctrineListRepresentationFactory;
    private $locationModel;

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
     * @throws EntityNotFoundException
     * @noinspection PhpUnusedParameterInspection
     */
    public function getAction(int $id, Request $request): Response
    {
        $entity = $this->locationModel->getLocation($id);
        return $this->handleView($this->view($entity));
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function postAction(Request $request): Response
    {
        $entity = $this->locationModel->createLocation($request);
        return $this->handleView($this->view($entity));
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws EntityNotFoundException
     */
    public function putAction(int $id, Request $request): Response
    {
        $entity = $this->locationModel->updateLocation($id, $request);
        return $this->handleView($this->view($entity));
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function deleteAction(int $id): Response
    {
        $this->locationModel->deleteLocation($id);
        return $this->handleView($this->view(null, 204));
    }

}
