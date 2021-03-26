<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Controller\Admin;

use Manuxi\SuluEventBundle\Common\DoctrineListRepresentationFactory;
use Manuxi\SuluEventBundle\Entity\Location;
use Manuxi\SuluEventBundle\Repository\LocationRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\View\ViewHandlerInterface;
use Sulu\Component\Rest\AbstractRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @RouteResource("location")
 */
class LocationController extends AbstractRestController implements ClassResourceInterface
{
    private $doctrineListRepresentationFactory;

    private $repository;

    public function __construct(
        DoctrineListRepresentationFactory $doctrineListRepresentationFactory,
        LocationRepository $repository,
        ViewHandlerInterface $viewHandler,
        ?TokenStorageInterface $tokenStorage = null
    ) {
        $this->doctrineListRepresentationFactory = $doctrineListRepresentationFactory;
        $this->repository                        = $repository;

        parent::__construct($viewHandler, $tokenStorage);
    }

    public function cgetAction(): Response
    {
        $listRepresentation = $this->doctrineListRepresentationFactory->createDoctrineListRepresentation(
            Location::RESOURCE_KEY
        );

        return $this->handleView($this->view($listRepresentation));
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function getAction(int $id, Request $request): Response
    {
        $entity = $this->load($id);
        if (!$entity) {
            throw new NotFoundHttpException();
        }

        return $this->handleView($this->view($entity));
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function postAction(Request $request): Response
    {
        $entity = $this->create();

        $this->mapDataToEntity($request->request->all(), $entity);

        $this->save($entity);

        return $this->handleView($this->view($entity));
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function putAction(int $id, Request $request): Response
    {
        $entity = $this->load($id);
        if (!$entity) {
            throw new NotFoundHttpException();
        }

        $this->mapDataToEntity($request->request->all(), $entity);

        $this->save($entity);

        return $this->handleView($this->view($entity));
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function deleteAction(int $id): Response
    {
        $this->remove($id);

        return $this->handleView($this->view());
    }

    /**
     * @param string[] $data
     */
    protected function mapDataToEntity(array $data, Location $entity): void
    {
        $entity->setName($data['name']);

        $entity->setStreet($data['street'] ?? null);
        $entity->setNumber($data['number'] ?? null);
        $entity->setCity($data['city'] ?? null);
        $entity->setPostalCode($data['postalCode'] ?? null);
        $entity->setCountryCode($data['countryCode'] ?? null);
    }

    protected function load(int $id): ?Location
    {
        return $this->repository->findById($id);
    }

    protected function create(): Location
    {
        return $this->repository->create();
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    protected function save(Location $entity): void
    {
        $this->repository->save($entity);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    protected function remove(int $id): void
    {
        $this->repository->remove($id);
    }
}
