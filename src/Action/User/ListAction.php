<?php

declare(strict_types=1);

namespace App\Action\User;

use App\Fixture\FixtureLoader;
use App\Entity\User;
use App\Responder\JsonHalResponder;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ListAction
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
        $users = $this->loader->makeMany(User::class, 5);

        $resource = new Collection($users, new \App\Transformer\Resource\UserTransformer());
        $data = $this->fractal->createData($resource)->toArray();

        return JsonHalResponder::collection('users', $data['data'] ?? [], [
            'total' => count($data['data'] ?? []),
            'count' => count($data['data'] ?? []),
        ]);
    }
}
