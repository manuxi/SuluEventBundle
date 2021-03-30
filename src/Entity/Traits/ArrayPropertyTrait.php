<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity\Traits;

trait ArrayPropertyTrait
{
    protected function getProperty(array $data, string $key, string $default = null): ?string
    {
        if (\array_key_exists($key, $data)) {
            return (string)$data[$key];
        }

        return $default;
    }

    protected function getPropertyMulti(array $data, array $keys, string $default = null): ?string
    {
        $currentKey = array_shift($keys);
        if(0 === count($keys)){
            if (\array_key_exists($currentKey, $data)) {
                return (string)$data[$currentKey];
            }
            return $default;
        } else {
            if(!\array_key_exists($currentKey, $data) || !\is_array($data[$currentKey])) {
                return null;
            } else {
                return $this->getPropertyMulti($data[$currentKey], $keys, $default);
            }
        }
    }

}
