<?php

declare(strict_types=1);

namespace App\Action\User;

use App\Fixture\FixtureLoader;
use App\Entity\User;
use App\Responder\JsonHalResponder;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PatchAction
{
    private FixtureLoader $loader;
    private Manager $fractal;

    public function __construct(FixtureLoader $loader)
    {
        $this->loader = $loader;
        $this->fractal = new Manager();
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $id = (int) ($request->getAttribute('id') ?? 0);

        if ($id <= 0) {
            return JsonHalResponder::badRequest('Invalid user ID provided');
        }

        $body = json_decode((string) $request->getBody(), true);

        if (empty($body)) {
            return JsonHalResponder::unprocessableEntity([['field' => 'body', 'message' => 'No fields provided']]);
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

        return JsonHalResponder::resource(
            'user',
            (string) $id,
            $data,
            [
                'collection' => '/api/users',
                'self' => "/api/users/{$id}",
            ]
        );
    }
}
