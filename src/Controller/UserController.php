<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class UserController
{
    private EntityManagerInterface $em;
    private UserRepository $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->repository = new UserRepository($em);
    }

    public function index(): array
    {
        $users = $this->repository->findAll();
        return ['users' => $users];
    }

    public function show(int $id): ?User
    {
        return $this->repository->find($id);
    }

    public function create(array $data): User
    {
        $user = new User();
        $user->setEmail($data['email']);
        $user->setPassword(password_hash($data['password'] ?? '', PASSWORD_BCRYPT));
        $user->setRoles($data['roles'] ?? ['ROLE_USER']);
        $user->setCreatedAt(new \DateTimeImmutable());

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    public function update(int $id, array $data): ?User
    {
        $user = $this->repository->find($id);
        
        if (!$user) {
            return null;
        }

        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }
        if (isset($data['password'])) {
            $user->setPassword(password_hash($data['password'], PASSWORD_BCRYPT));
        }
        if (isset($data['roles'])) {
            $user->setRoles($data['roles']);
        }
        $user->setUpdatedAt(new \DateTimeImmutable());

        $this->em->flush();

        return $user;
    }

    public function delete(int $id): bool
    {
        $user = $this->repository->find($id);
        
        if (!$user) {
            return false;
        }

        $this->em->remove($user);
        $this->em->flush();

        return true;
    }
}
