<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Admin;

use Manuxi\SuluEventBundle\Entity\Event;
use Sulu\Bundle\AdminBundle\Admin\Admin;
use Sulu\Bundle\AdminBundle\Admin\View\ViewCollection;
use Sulu\Bundle\AutomationBundle\Admin\AutomationAdmin;
use Sulu\Bundle\AutomationBundle\Admin\View\AutomationViewBuilderFactoryInterface;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;

class EventAutomationAdmin extends Admin
{
    public function __construct(
        private readonly AutomationViewBuilderFactoryInterface $automationViewBuilderFactory,
        private readonly SecurityCheckerInterface $securityChecker,
    ) {
    }

    public function configureViews(ViewCollection $viewCollection): void
    {
        if ($viewCollection->has(EventAdmin::EDIT_TABS_VIEW)
            && $this->securityChecker->hasPermission(AutomationAdmin::SECURITY_CONTEXT, PermissionTypes::EDIT)
        ) {
            $viewCollection->add(
                $this->automationViewBuilderFactory->createTaskListViewBuilder(
                    EventAdmin::EDIT_TABS_VIEW . '.automation',
                    '/automation',
                    Event::class,
                )
                    ->setTabOrder(4608)
                    ->setParent(EventAdmin::EDIT_TABS_VIEW),
            );
        }
    }
}
