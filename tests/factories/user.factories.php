<?php

declare(strict_types=1);

use App\Entity\User;

$fm->define(User::class)->setDefinitions([
    'email' => 'user{++}@example.com',
    'password' => 'password123',
    'roles' => ['ROLE_USER'],
    'createdAt' => fn() => new \DateTimeImmutable(),
    'updatedAt' => null,
])->setCallback(function (User $user) {
    $user->setGroup(null);
});
