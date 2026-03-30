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
 * ADR Action: Create new user.
 */
class CreateAction
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
        $data = $this->getParsedBody($request);

        if ($data === null) {
            return $this->badRequest('Invalid JSON body');
        }

        $errors = $this->validate($data);
        if (!empty($errors)) {
            return $this->validationError($errors);
        }

        $user = new User();
        $user->setEmail($data['email']);
        $user->setPassword(password_hash($data['password'] ?? '', PASSWORD_DEFAULT));
        $user->setRoles($data['roles'] ?? ['ROLE_USER']);
        $user->setCreatedAt(new \DateTimeImmutable());

        try {
            $this->repository->save($user, true);
        } catch (\Throwable $e) {
            return $this->serverError('Failed to create user: ' . $e->getMessage());
        }

        $resource = new Item($user, new \App\Transformer\Resource\UserTransformer());
        $result = $this->fractal->createData($resource)->toArray();

        return new JsonResponse($result, 201);
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

    private function validate(array $data): array
    {
        $errors = [];
        
        if (empty($data['email'])) {
            $errors[] = ['field' => 'email', 'message' => 'Email is required'];
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = ['field' => 'email', 'message' => 'Invalid email format'];
        }
        
        if (empty($data['password'])) {
            $errors[] = ['field' => 'password', 'message' => 'Password is required'];
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
