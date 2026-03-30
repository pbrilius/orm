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

class ShowAction
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

        $user = $this->loader->make(User::class);
        $user->setEmail("user{$id}@example.com");

        $resource = new Item($user, new \App\Transformer\Resource\UserTransformer());
        $data = $this->fractal->createData($resource)->toArray();

        return new JsonResponse($data, 200, [
            'Content-Type' => 'application/vnd.api+json',
        ]);
    }
}
