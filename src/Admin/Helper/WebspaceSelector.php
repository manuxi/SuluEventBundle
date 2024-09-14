<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Admin\Helper;

use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;

class WebspaceSelector
{
    public function __construct(private WebspaceManagerInterface $webspaceManager)
    {}

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
