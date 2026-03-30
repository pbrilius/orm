<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Fixture\FixtureLoader;
use App\Entity\User;
use App\Entity\Post;
use App\Entity\Group;

class FixtureLoaderTest extends TestCase
{
    private FixtureLoader $loader;

    protected function setUp(): void
    {
        $this->loader = new FixtureLoader();
    }

    public function testMakeUser(): void
    {
        $user = $this->loader->make(User::class);
        
        $this->assertInstanceOf(User::class, $user);
        $this->assertNotEmpty($user->getEmail());
        $this->assertStringContainsString('@example.com', $user->getEmail());
    }

    public function testMakePost(): void
    {
        $post = $this->loader->make(Post::class);
        
        $this->assertInstanceOf(Post::class, $post);
        $this->assertNotEmpty($post->getTitle());
        $this->assertNotEmpty($post->getContent());
    }

    public function testMakeGroup(): void
    {
        $group = $this->loader->make(Group::class);
        
        $this->assertInstanceOf(Group::class, $group);
        $this->assertNotEmpty($group->getName());
    }

    public function testMakeManyUsers(): void
    {
        $users = $this->loader->makeMany(User::class, 3);
        
        $this->assertCount(3, $users);
        foreach ($users as $user) {
            $this->assertInstanceOf(User::class, $user);
        }
    }

    public function testMakeManyPosts(): void
    {
        $posts = $this->loader->makeMany(Post::class, 5);
        
        $this->assertCount(5, $posts);
        foreach ($posts as $post) {
            $this->assertInstanceOf(Post::class, $post);
        }
    }
}
