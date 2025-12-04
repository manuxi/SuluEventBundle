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

        $listResponse = $listBuilder->execute();

        return $this->handleView($this->view($listResponse));
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

    private function mapDataToEntity(array $data, Location $location): void
    {
        // Basic address fields
        if (array_key_exists('name', $data)) {
            $location->setName($data['name']);
        }

        if (array_key_exists('street', $data)) {
            $location->setStreet($data['street']);
        }

        if (array_key_exists('number', $data)) {
            $location->setNumber($data['number']);
        }

        if (array_key_exists('postalCode', $data)) {
            $location->setPostalCode($data['postalCode']);
        }

        if (array_key_exists('city', $data)) {
            $location->setCity($data['city']);
        }

        if (array_key_exists('state', $data)) {
            $location->setState($data['state']);
        }

        if (array_key_exists('countryCode', $data)) {
            $location->setCountryCode($data['countryCode']);
        }

        // Contact info
        if (array_key_exists('email', $data)) {
            $location->setEmail($data['email']);
        }

        if (array_key_exists('phoneNumber', $data)) {
            $location->setPhoneNumber($data['phoneNumber']);
        }

        // Link
        if (array_key_exists('link', $data)) {
            $location->setLink($data['link']);
        }

        // Coordinates
        if (array_key_exists('location', $data)) {
            $location->setLocation($data['location']);
        }

        // Notes
        if (array_key_exists('notes', $data)) {
            $location->setNotes($data['notes']);
        }

        // Image (single)
        if (array_key_exists('image', $data)) {
            if (isset($data['image']['id'])) {
                $image = $this->entityManager->find(MediaInterface::class, $data['image']['id']);
                $location->setImage($image);
            } else {
                $location->setImage(null);
            }
        }

        // Images (gallery)
        if (array_key_exists('images', $data)) {
            $location->setImages($data['images']);
        }

        // PDF
        if (array_key_exists('pdf', $data)) {
            if (isset($data['pdf']['id'])) {
                $pdf = $this->entityManager->find(MediaInterface::class, $data['pdf']['id']);
                $location->setPdf($pdf);
            } else {
                $location->setPdf(null);
            }
        }
    }
}