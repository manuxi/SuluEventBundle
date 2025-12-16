<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\View\ViewHandlerInterface;
use Manuxi\SuluEventBundle\Entity\Location;
use Manuxi\SuluEventBundle\Repository\LocationRepository;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Sulu\Bundle\MediaBundle\Media\Manager\MediaManagerInterface;
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
        private readonly MediaManagerInterface $mediaManager,
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
        $locale = $request->query->get('locale', 'en');

        $fieldDescriptors = $this->fieldDescriptorFactory->getFieldDescriptors(Location::RESOURCE_KEY);
        $listBuilder = $this->listBuilderFactory->create(Location::class);
        $this->restHelper->initializeListBuilder($listBuilder, $fieldDescriptors);

        $listElements = $listBuilder->execute();

        // Resolve thumbnail URLs
        $listElements = $this->addImagesToListElements($listElements, $locale);

        $listRepresentation = new PaginatedRepresentation(
            $listElements,
            Location::RESOURCE_KEY,
            (int) $listBuilder->getCurrentPage(),
            (int) $listBuilder->getLimit(),
            $listBuilder->count()
        );

        return $this->handleView($this->view($listRepresentation));
    }

    private function addImagesToListElements(array $listElements, string $locale): array
    {
        $ids = array_filter(array_column($listElements, 'image'));

        if (empty($ids)) {
            return $listElements;
        }

        $images = $this->mediaManager->getFormatUrls($ids, $locale);

        foreach ($listElements as $key => $element) {
            if (
                \array_key_exists('image', $element)
                && $element['image']
                && \array_key_exists($element['image'], $images)
            ) {
                $listElements[$key]['image'] = $images[$element['image']];
            }
        }

        return $listElements;
    }

    #[Route(
        '/locations/{id}.{_format}',
        name: 'sulu_event.get_location',
        requirements: ['id' => '\d+', '_format' => 'json'],
        defaults: ['_format' => 'json'],
        methods: ['GET']
    )]
    public function getAction(int $id, Request $request): Response
    {
        $location = $this->locationRepository->find($id);

        if (!$location) {
            throw new NotFoundHttpException();
        }

        $apiLocation = $this->resolveLocationMedia($location, $request->query->get('locale'));

        return $this->handleView($this->view($apiLocation));
    }

    private function resolveLocationMedia(Location $location, ?string $locale): array
    {
        $data = [
            'id' => $location->getId(),
            'name' => $location->getName(),
            'street' => $location->getStreet(),
            'number' => $location->getNumber(),
            'postalCode' => $location->getPostalCode(),
            'city' => $location->getCity(),
            'state' => $location->getState(),
            'countryCode' => $location->getCountryCode(),
            'notes' => $location->getNotes(),
            'email' => $location->getEmail(),
            'phoneNumber' => $location->getPhoneNumber(),
            'link' => $location->getLink(),
            'location' => $location->getLocation(),
            'images' => $location->getImages(),
        ];

        if ($image = $location->getImage()) {
            try {
                $apiMedia = $this->mediaManager->getById($image->getId(), $locale ?? 'en');
                $data['image'] = $apiMedia;
            } catch (\Exception $e) {
                $data['image'] = ['id' => $image->getId()];
            }
        }

        if ($pdf = $location->getPdf()) {
            try {
                $apiMedia = $this->mediaManager->getById($pdf->getId(), $locale ?? 'en');
                $data['pdf'] = $apiMedia;
            } catch (\Exception $e) {
                $data['pdf'] = ['id' => $pdf->getId()];
            }
        }

        return $data;
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

        $apiLocation = $this->resolveLocationMedia($location, $request->query->get('locale'));

        return $this->handleView($this->view($apiLocation, 201));
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

        $apiLocation = $this->resolveLocationMedia($location, $request->query->get('locale'));

        return $this->handleView($this->view($apiLocation));
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
        $entity->setLink($data['link'] ?? null);

        if (array_key_exists('image', $data)) {
            $image = null;
            if (isset($data['image']['id'])) {
                $image = $this->entityManager->getReference(MediaInterface::class, $data['image']['id']);
            }
            $entity->setImage($image);
        }

        if (array_key_exists('pdf', $data)) {
            $pdf = null;
            if (isset($data['pdf']['id'])) {
                $pdf = $this->entityManager->getReference(MediaInterface::class, $data['pdf']['id']);
            }
            $entity->setPdf($pdf);
        }
    }
}