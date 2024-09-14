<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Controller\Website;

use Exception;
use Sulu\Bundle\HttpCacheBundle\Cache\SuluHttpCache;
use Sulu\Bundle\MediaBundle\Media\Manager\MediaManagerInterface;
use Sulu\Bundle\WebsiteBundle\Controller\WebsiteController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

abstract class AbstractController extends WebsiteController
{
    protected ?Request $request;

    public function __construct(
        RequestStack $requestStack,
        protected MediaManagerInterface $mediaManager
    ) {
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * @param string $viewTemplate
     * @param array $parameters
     * @param bool $preview
     * @param bool $partial
     * @return Response
     * @throws Exception
     */
    protected function prepareResponse(string $viewTemplate, array $parameters, bool $preview, bool $partial): Response
    {
        $response = $this->createResponse($this->request);

        try {
            if ($partial) {
                $response->setContent(
                    $this->renderBlockView(
                        $viewTemplate,
                        'content',
                        $parameters
                    )
                );

                return $response;
            } elseif ($preview) {
                $response->setContent(
                    $this->renderPreview($viewTemplate, $parameters)
                );
            } else {
                $response->setContent(
                    $this->renderView(
                        $viewTemplate,
                        $parameters
                    )
                );
            }
            return $response;

        } catch (\InvalidArgumentException $exception) {
            // template not found
            throw new HttpException(406, 'Error encountered while rendering content.', $exception);
        }
    }

    private function createResponse(Request $request): Response
    {
        $response = new Response();
        $cacheLifetime = $request->attributes->get('_cacheLifetime');

        if ($cacheLifetime) {
            $response->setPublic();
            $response->headers->set(
                SuluHttpCache::HEADER_REVERSE_PROXY_TTL,
                $cacheLifetime
            );
            $response->setMaxAge($this->getParameter('sulu_http_cache.cache.max_age'));
            $response->setSharedMaxAge($this->getParameter('sulu_http_cache.cache.shared_max_age'));
        }

        // we need to set the content type ourselves here
        // else symfony will use the accept header of the client and the page could be cached with false content-type
        // see following symfony issue: https://github.com/symfony/symfony/issues/35694
        $mimeType = $request->getMimeType($request->getRequestFormat());

        if ($mimeType) {
            $response->headers->set('Content-Type', $mimeType);
        }

        return $response;
    }

    protected function getViewTemplate(string $view, Request $request, bool $preview = false): string
    {
        if (!$preview) {
            $requestFormat = $request->getRequestFormat();
        } else {
            $requestFormat = 'html';
        }
        return $view . '.' . $requestFormat . '.twig';
    }

}
