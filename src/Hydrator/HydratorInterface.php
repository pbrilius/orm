<?php

declare(strict_types=1);

namespace Oryx\ORM\Hydrator;

interface HydratorInterface
{
    /**
     * Hydrate an object with the given data.
     *
     * @param object $object The object to hydrate.
     * @param array $data The data to hydrate the object with.
     * @return object The hydrated object.
     */
    public function hydrate(object $object, array $data): object;

    /**
     * Extract data from an object.
     *
     * @param object $object The object to extract data from.
     * @return array The extracted data.
     */
    public function extract(object $object): array;
}
