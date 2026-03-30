<?php

declare(strict_types=1);

namespace App\Action\User;

use App\Entity\User;
use App\Repository\UserRepository;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;

/**
 * ADR Action: Update existing user.
 */
class UpdateAction
{
    private UserRepository $repository;
    private Manager $fractal;

    public function __construct(UserRepository $repository, Manager $fractal)
    {
        $this->repository = $repository;
        $this->fractal = $fractal;
    }

    public function __invoke(ServerRequestInterface $request): JsonResponse
    {
        $id = (int) $request->getAttribute('id');

        $user = $this->repository->find($id);

        if (!$user) {
            return $this->notFound($id);
        }

        $data = $this->getParsedBody($request);

        if ($data === null) {
            return $this->badRequest('Invalid JSON body');
        }

        $errors = $this->validate($data, true);
        if (!empty($errors)) {
            return $this->validationError($errors);
        }

        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }

        if (isset($data['password'])) {
            $user->setPassword(password_hash($data['password'], PASSWORD_DEFAULT));
        }

        if (isset($data['roles'])) {
            $user->setRoles($data['roles']);
        }

        $user->setUpdatedAt(new \DateTime());

        try {
            $this->repository->save($user, true);
        } catch (\Throwable $e) {
            return $this->serverError('Failed to update user: ' . $e->getMessage());
        }

        $resource = new Item($user, new \App\Transformer\Resource\UserTransformer());
        $result = $this->fractal->createData($resource)->toArray();

        return new JsonResponse($result, 200);
    }

    private function getParsedBody(ServerRequestInterface $request): ?array
    {
        $body = $request->getBody()->getContents();
        if (empty($body)) {
            return [];
        }

        $data = json_decode($body, true);
        return json_last_error() === JSON_ERROR_NONE ? $data : null;
    }

    private function validate(array $data, bool $isUpdate = false): array
    {
        $errors = [];

        if (isset($data['email'])) {
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = ['field' => 'email', 'message' => 'Invalid email format'];
            }
        }

        return $errors;
    }

    private function validationError(array $errors): JsonResponse
    {
        return new JsonResponse([
            'error' => [
                'type' => 'about:blank',
                'title' => 'Validation Failed',
                'detail' => 'The given data was invalid.',
                'status' => 422,
                'errors' => $errors,
            ],
        ], 422);
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

    private function badRequest(string $message): JsonResponse
    {
        return new JsonResponse([
            'error' => [
                'type' => 'about:blank',
                'title' => 'Bad Request',
                'detail' => $message,
                'status' => 400,
            ],
        ], 400);
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
