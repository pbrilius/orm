<?php

declare(strict_types=1);

use App\Entity\Group;

$fm->define(Group::class)->setDefinitions([
    'name' => 'Group {++}',
    'createdAt' => fn() => new \DateTimeImmutable(),
]);
