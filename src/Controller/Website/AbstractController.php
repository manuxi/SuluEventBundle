<?php

namespace Manuxi\SuluEventBundle\Controller\Website;

use Sulu\Bundle\MediaBundle\Api\Media;
use Sulu\Bundle\MediaBundle\Media\Manager\MediaManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as AbstractSymfonyController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

abstract class AbstractController extends AbstractSymfonyController
{
    protected $mediaManager;

    /**
     * @var Request|null
     */
    protected $request;

    public function __construct(
        RequestStack $requestStack,
        MediaManagerInterface $mediaManager
    ) {
        $this->request      = $requestStack->getCurrentRequest();
        $this->mediaManager = $mediaManager;
    }

    protected function getBannerMedia(array $bannerIds): array
    {
        $media = [];
        foreach ($bannerIds as $id) {
            $mediaEntity = $this->mediaManager->getEntityById($id);
            $media[]     = $this->mediaManager->addFormatsAndUrl(new Media($mediaEntity, $this->request->getLocale(), null));
        }

        return $media;
    }
}
