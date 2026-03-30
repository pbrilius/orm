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
 * ADR Action: Show single user.
 */
class ShowAction
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

        $resource = new Item($user, new \App\Transformer\Resource\UserTransformer());
        $data = $this->fractal->createData($resource)->toArray();

        return new JsonResponse($data, 200);
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
}
