<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Service;

use Symfony\Component\Intl\Countries;

class CountryCodeSelect
{

    public function getValues(): array
    {
        $values = [];

        foreach (Countries::getNames() as $code => $title) {
            $values[] = [
                'name'  => $code,
                'title' => $title,
            ];
        }

        return $values;
    }
}
