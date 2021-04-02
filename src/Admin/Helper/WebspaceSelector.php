<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Admin\Helper;

use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;

class WebspaceSelector
{

    private $webspaceManager;

    public function __construct(WebspaceManagerInterface $webspaceManager)
    {
        $this->webspaceManager = $webspaceManager;
    }

    public function getValues(): array
    {
        $values = [];
        foreach ($this->webspaceManager->getWebspaceCollection() as $webspace) {
            $values[] = [
                'name' => $webspace->getKey(),
                'title' => $webspace->getName(),
            ];
        }

        return $values;
    }
}
