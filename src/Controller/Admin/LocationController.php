<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Controller\Admin;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\View\ViewHandlerInterface;
use Manuxi\SuluEventBundle\Entity\Location;
use Manuxi\SuluEventBundle\Entity\Models\LocationModel;
use Manuxi\SuluEventBundle\ListBuilder\DoctrineListRepresentationFactory;
use Sulu\Bundle\TrashBundle\Application\TrashManager\TrashManagerInterface;
use Sulu\Component\Rest\AbstractRestController;
use Sulu\Component\Rest\Exception\EntityNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[Route('/admin/api')]
class LocationController extends AbstractRestController implements ClassResourceInterface
{
    public function __construct(
        private readonly LocationModel $locationModel,
        private readonly DoctrineListRepresentationFactory $doctrineListRepresentationFactory,
        private readonly TrashManagerInterface $trashManager,
        ViewHandlerInterface $viewHandler,
        ?TokenStorageInterface $tokenStorage = null,
    ) {
        parent::__construct($viewHandler, $tokenStorage);
    }

    #[Route(
        '/locations.{_format}',
        name: 'sulu_event.get_locations',
        requirements: [
            'id' => '\d+',
            '_format' => 'json|csv',
        ],
        options: ['expose' => true],
        defaults: [
            '_format' => 'json',
        ],
        methods: ['GET']
    )]
    public function cgetAction(): Response
    {
        $listRepresentation = $this->doctrineListRepresentationFactory->createDoctrineListRepresentation(
            Location::RESOURCE_KEY
        );

        return $this->handleView($this->view($listRepresentation));
    }

    /**
     * @throws EntityNotFoundException
     *
     * @noinspection PhpUnusedParameterInspection
     */
    #[Route(
        '/locations/{id}.{_format}',
        name: 'sulu_event.get_location',
        requirements: [
            'id' => '\d+',
            '_format' => 'json|csv',
        ],
        options: ['expose' => true],
        defaults: [
            '_format' => 'json',
        ],
        methods: ['GET']
    )]
    public function getAction(int $id, Request $request): Response
    {
        $entity = $this->locationModel->getLocation($id);

        return $this->handleView($this->view($entity));
    }

    /**
     * @throws EntityNotFoundException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    #[Route(
        '/events.{_format}',
        name: 'sulu_event.post_location',
        requirements: ['_format' => 'json'],
        options: ['expose' => true],
        defaults: ['_format' => 'json'],
        methods: ['POST']
    )]
    public function postAction(Request $request): Response
    {
        $entity = $this->locationModel->createLocation($request);

        return $this->handleView($this->view($entity));
    }

    /**
     * @throws EntityNotFoundException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    #[Route(
        '/locations/{id}.{_format}',
        name: 'sulu_event.put_location',
        requirements: [
            'id' => '\d+',
            '_format' => 'json',
        ],
        options: ['expose' => true],
        defaults: ['_format' => 'json'],
        methods: ['PUT']
    )]
    public function putAction(int $id, Request $request): Response
    {
        $entity = $this->locationModel->updateLocation($id, $request);

        return $this->handleView($this->view($entity));
    }

    /**
     * @throws EntityNotFoundException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    #[Route(
        '/locations/{id}.{_format}',
        name: 'sulu_event.delete_location',
        requirements: [
            'id' => '\d+',
            '_format' => 'json',
        ],
        options: ['expose' => true],
        defaults: ['_format' => 'json'],
        methods: ['DELETE']
    )]
    public function deleteAction(int $id): Response
    {
        $entity = $this->locationModel->getLocation($id);
        $this->trashManager->store(Location::RESOURCE_KEY, $entity);

        $title = $entity->getName() ?? 'n.a.';
        $this->locationModel->deleteLocation($id, $title);

        return $this->handleView($this->view(null, 204));
    }
}
