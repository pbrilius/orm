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

class ShowAction
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

        $user = $this->loader->make(User::class);
        $user->setEmail("user{$id}@example.com");

        $resource = new Item($user, new \App\Transformer\Resource\UserTransformer());
        $data = $this->fractal->createData($resource)->toArray();

        return JsonHalResponder::resource(
            'user',
            (string) $id,
            $data,
            [
                'collection' => '/api/users',
            ]
        );
    }
}
