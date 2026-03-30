<?php

declare(strict_types=1);

use App\Entity\Post;

$fm->define(Post::class)->setDefinitions([
    'title' => 'Post Title {++}',
    'content' => 'This is the content of post number {++}.',
    'createdAt' => fn() => new \DateTimeImmutable(),
    'updatedAt' => null,
    'author' => null,
]);
