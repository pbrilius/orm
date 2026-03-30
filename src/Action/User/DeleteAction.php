<?php

declare(strict_types=1);

namespace App\Action\User;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\JsonResponse;

class DeleteAction
{
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

        return new JsonResponse(null, 204);
    }
}
