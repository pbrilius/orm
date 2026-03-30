<?php

declare(strict_types=1);

namespace App\Action\User;

use App\Entity\User;
use App\Repository\UserRepository;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;

/**
 * ADR Action: Delete existing user.
 */
class DeleteAction
{
    private UserRepository $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(ServerRequestInterface $request): JsonResponse
    {
        $id = (int) $request->getAttribute('id');

        $user = $this->repository->find($id);

        if (!$user) {
            return $this->notFound($id);
        }

        try {
            $this->repository->remove($user, true);
        } catch (\Throwable $e) {
            return $this->serverError('Failed to delete user: ' . $e->getMessage());
        }

        return new JsonResponse(null, 204);
    }

    private function notFound(int $id): JsonResponse
    {
        return new JsonResponse([
            'error' => [
                'type' => 'about:blank',
                'title' => 'Not Found',
                'detail' => "User with ID {$id} not found.",
                'status' => 404,
            ],
        ], 404);
    }

    private function serverError(string $message): JsonResponse
    {
        return new JsonResponse([
            'error' => [
                'type' => 'about:blank',
                'title' => 'Internal Server Error',
                'detail' => $message,
                'status' => 500,
            ],
        ], 500);
    }
}
