<?php

declare(strict_types=1);

namespace Oryx\ORM\Hydrator;

class ArrayHydrator implements HydratorInterface
{
    public function hydrate(object $object, array $data): object
    {
        foreach ($data as $property => $value) {
            if (property_exists($object, $property)) {
                $object->{$property} = $value;
            }
        }

        return $object;
    }

    public function extract(object $object): array
    {
        $data = [];

        foreach (get_object_vars($object) as $property => $value) {
            $data[$property] = $value;
        }

        return $data;
    }
}
