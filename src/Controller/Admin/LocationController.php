<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\View\ViewHandlerInterface;
use Manuxi\SuluEventBundle\Entity\Location;
use Manuxi\SuluEventBundle\Repository\LocationRepository;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Sulu\Bundle\TrashBundle\Application\TrashManager\TrashManagerInterface;
use Sulu\Component\Rest\AbstractRestController;
use Sulu\Component\Rest\ListBuilder\Doctrine\DoctrineListBuilderFactoryInterface;
use Sulu\Component\Rest\ListBuilder\Metadata\FieldDescriptorFactoryInterface;
use Sulu\Component\Rest\ListBuilder\PaginatedRepresentation;
use Sulu\Component\Rest\RequestParametersTrait;
use Sulu\Component\Rest\RestHelperInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[Route(path: '/admin/api')]
class LocationController extends AbstractRestController
{
    use RequestParametersTrait;

    public function __construct(
        private readonly LocationRepository $locationRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly TrashManagerInterface $trashManager,
        private readonly FieldDescriptorFactoryInterface $fieldDescriptorFactory,
        private readonly DoctrineListBuilderFactoryInterface $listBuilderFactory,
        private readonly RestHelperInterface $restHelper,
        ViewHandlerInterface $viewHandler,
        TokenStorageInterface $tokenStorage,
    ) {
        parent::__construct($viewHandler, $tokenStorage);
    }

    #[Route(
        '/locations.{_format}',
        name: 'sulu_event.get_locations',
        requirements: ['_format' => 'json'],
        defaults: ['_format' => 'json'],
        methods: ['GET']
    )]
    public function cgetAction(Request $request): Response
    {
        $fieldDescriptors = $this->fieldDescriptorFactory->getFieldDescriptors(Location::RESOURCE_KEY);
        $listBuilder = $this->listBuilderFactory->create(Location::class);
        $this->restHelper->initializeListBuilder($listBuilder, $fieldDescriptors);

        $listRepresentation = new PaginatedRepresentation(
            $listBuilder->execute(),
            Location::RESOURCE_KEY,
            (int) $listBuilder->getCurrentPage(),
            (int) $listBuilder->getLimit(),
            $listBuilder->count()
        );

        return $this->handleView($this->view($listRepresentation));
    }

    #[Route(
        '/locations/{id}.{_format}',
        name: 'sulu_event.get_location',
        requirements: ['id' => '\d+', '_format' => 'json'],
        defaults: ['_format' => 'json'],
        methods: ['GET']
    )]
    public function getAction(int $id): Response
    {
        $location = $this->locationRepository->find($id);

        if (!$location) {
            throw new NotFoundHttpException();
        }

        return $this->handleView($this->view($location));
    }

    #[Route(
        '/locations.{_format}',
        name: 'sulu_event.post_location',
        requirements: ['_format' => 'json'],
        defaults: ['_format' => 'json'],
        methods: ['POST']
    )]
    public function postAction(Request $request): Response
    {
        $location = new Location();
        $this->mapDataToEntity($request->request->all(), $location);

        $this->entityManager->persist($location);
        $this->entityManager->flush();

        return $this->handleView($this->view($location, 201));
    }

    #[Route(
        '/locations/{id}.{_format}',
        name: 'sulu_event.put_location',
        requirements: ['id' => '\d+', '_format' => 'json'],
        defaults: ['_format' => 'json'],
        methods: ['PUT']
    )]
    public function putAction(int $id, Request $request): Response
    {
        $location = $this->locationRepository->find($id);

        if (!$location) {
            throw new NotFoundHttpException();
        }

        $this->mapDataToEntity($request->request->all(), $location);

        $this->entityManager->flush();

        return $this->handleView($this->view($location));
    }

    #[Route(
        '/locations/{id}.{_format}',
        name: 'sulu_event.delete_location',
        requirements: ['id' => '\d+', '_format' => 'json'],
        defaults: ['_format' => 'json'],
        methods: ['DELETE']
    )]
    public function deleteAction(int $id): Response
    {
        $location = $this->locationRepository->find($id);

        if (!$location) {
            throw new NotFoundHttpException();
        }

        $this->trashManager->store(Location::RESOURCE_KEY, $location);
        $this->entityManager->remove($location);
        $this->entityManager->flush();

        return $this->handleView($this->view(null, 204));
    }

    protected function mapDataToEntity(array $data, Location $entity): void
    {
        $entity->setName($data['name'] ?? null);
        $entity->setStreet($data['street'] ?? null);
        $entity->setNumber($data['number'] ?? null);
        $entity->setPostalCode($data['postalCode'] ?? null);
        $entity->setCity($data['city'] ?? null);
        $entity->setState($data['state'] ?? null);
        $entity->setCountryCode($data['countryCode'] ?? null);
        $entity->setNotes($data['notes'] ?? null);
        $entity->setEmail($data['email'] ?? null);
        $entity->setPhoneNumber($data['phoneNumber'] ?? null);
        $entity->setLocation($data['location'] ?? null);
        $entity->setImages($data['images'] ?? null);

        if (isset($data['image']) && is_array($data['image']) && isset($data['image']['id'])) {
            $image = $this->entityManager->getReference(MediaInterface::class, $data['image']['id']);
            $entity->setImage($image);
        }

        if (isset($data['pdf']) && is_array($data['pdf']) && isset($data['pdf']['id'])) {
            $pdf = $this->entityManager->getReference(MediaInterface::class, $data['pdf']['id']);
            $entity->setPdf($pdf);
        }
    }
}