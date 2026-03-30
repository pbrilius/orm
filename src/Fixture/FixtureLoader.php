<?php

declare(strict_types=1);

namespace App\Fixture;

use League\FactoryMuffin\FactoryMuffin;
use Doctrine\ORM\EntityManagerInterface;

class FixtureLoader
{
    private FactoryMuffin $factoryMuffin;
    private ?EntityManagerInterface $entityManager;

    public function __construct(?EntityManagerInterface $entityManager = null)
    {
        $this->entityManager = $entityManager;
        $this->factoryMuffin = new FactoryMuffin();
        $this->loadFactories();
    }

    private function loadFactories(): void
    {
        $factoriesDir = dirname(__DIR__, 2) . '/tests/factories';
        $this->factoryMuffin->loadFactories($factoriesDir);
    }

    public function create(string $name, array $attributes = []): object
    {
        $entity = $this->factoryMuffin->create($name, $attributes);
        return $entity;
    }

    public function createAndPersist(string $name, array $attributes = []): object
    {
        $entity = $this->create($name, $attributes);
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
        return $entity;
    }

    public function createMany(string $name, int $count, array $attributes = []): array
    {
        return $this->factoryMuffin->seed($count, $name, $attributes, true);
    }

    public function createManyAndPersist(string $name, int $count, array $attributes = []): array
    {
        $entities = $this->createMany($name, $count, $attributes);
        foreach ($entities as $entity) {
            $this->entityManager->persist($entity);
        }
        $this->entityManager->flush();
        return $entities;
    }

    public function make(string $name, array $attributes = []): object
    {
        return $this->factoryMuffin->seed(1, $name, $attributes, false)[0];
    }

    public function makeMany(string $name, int $count, array $attributes = []): array
    {
        return $this->factoryMuffin->seed($count, $name, $attributes, false);
    }

    public function define(string $name, string $class, array $attributes): void
    {
        $this->factoryMuffin->define($class)->setDefinitions($attributes);
    }

    public function resetDb(): void
    {
        $connection = $this->entityManager->getConnection();
        $platform = $connection->getDatabasePlatform();
        
        $connection->executeStatement(
            $platform->getTruncateTableSQL('users', true)
        );
        $connection->executeStatement(
            $platform->getTruncateTableSQL('posts', true)
        );
        $connection->executeStatement(
            $platform->getTruncateTableSQL('`groups`', true)
        );
    }

    public function getFactory(): FactoryMuffin
    {
        return $this->factoryMuffin;
    }
}
