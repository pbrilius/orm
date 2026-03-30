<?php

declare(strict_types=1);

namespace App\Action\User;

use App\Fixture\FixtureLoader;
use App\Entity\User;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use League\Fractal\Serializer\JsonApiSerializer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\JsonResponse;

class PatchAction
{
    private FixtureLoader $loader;
    private Manager $fractal;

    public function __construct(FixtureLoader $loader)
    {
        $this->loader = $loader;
        $this->fractal = new Manager();
        $this->fractal->setSerializer(new JsonApiSerializer());
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $id = (int) ($request->getAttribute('id') ?? 0);
        
        if ($id <= 0) {
            return new JsonResponse([
                'errors' => [[
                    'status' => '400',
                    'title' => 'Bad Request',
                    'detail' => 'Invalid user ID provided',
                ]],
            ], 400);
        }

        $body = json_decode((string) $request->getBody(), true);

        if (empty($body)) {
            return new JsonResponse([
                'errors' => [[
                    'status' => '422',
                    'title' => 'Unprocessable Entity',
                    'detail' => 'No fields provided for update',
                ]],
            ], 422);
        }

        $user = $this->loader->make(User::class);
        $user->setEmail($body['email'] ?? "user{$id}@example.com");
        
        if (isset($body['password'])) {
            $user->setPassword(password_hash($body['password'], PASSWORD_BCRYPT));
        }
        
        if (isset($body['roles'])) {
            $user->setRoles($body['roles']);
        }

        $resource = new Item($user, new \App\Transformer\Resource\UserTransformer());
        $data = $this->fractal->createData($resource)->toArray();

        return new JsonResponse($data, 200, [
            'Content-Type' => 'application/vnd.api+json',
        ]);
    }
}
