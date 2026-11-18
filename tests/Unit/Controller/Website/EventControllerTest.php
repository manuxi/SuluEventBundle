<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Tests\Unit\Controller\Website;

use Manuxi\SuluEventBundle\Controller\Website\EventController;
use Manuxi\SuluEventBundle\Entity\Event;
use Manuxi\SuluEventBundle\Entity\EventDimensionContent;
use PHPUnit\Framework\TestCase;
use Sulu\Bundle\WebsiteBundle\Resolver\TemplateAttributeResolverInterface;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Route\Domain\Repository\RouteRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use Twig\Environment;

class EventControllerTest extends TestCase
{
    public function testTheWebsiteNeverFallsBackToTheDraft(): void
    {
        $event = new Event();
        $this->addRow($event, DimensionContentInterface::STAGE_DRAFT, 'Draft title');
        // the aggregated content has no title (nothing live in this language): the controller looks in the collection
        $object = new EventDimensionContent($event);
        $object->setLocale('de');

        $this->expectException(NotAcceptableHttpException::class);

        $this->controller()->indexAction($object);
    }

    private function addRow(Event $event, string $stage, string $title): void
    {
        $row = new EventDimensionContent($event);
        $row->setStage($stage);
        $row->setLocale('de');
        $row->setTitle($title);
        $event->addDimensionContent($row);
    }

    private function controller(): EventController
    {
        $request = new Request();
        $request->setLocale('de');
        $stack = new RequestStack();
        $stack->push($request);

        $resolver = $this->createStub(TemplateAttributeResolverInterface::class);
        $resolver->method('resolve')->willThrowException(new \LogicException('the template must not be rendered'));

        return new EventController(
            $this->createStub(Environment::class),
            $resolver,
            $this->createStub(RouteRepositoryInterface::class),
            $this->createStub(WebspaceManagerInterface::class),
            $stack,
        );
    }
}
