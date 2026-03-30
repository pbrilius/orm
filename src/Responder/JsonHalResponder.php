<?php

declare(strict_types=1);

namespace App\Responder;

use Laminas\Diactoros\Response\JsonResponse;

class JsonHalResponder
{
    public static function resource(
        string $type,
        string $id,
        array $attributes,
        array $links = [],
        array $embedded = []
    ): JsonResponse {
        $data = [
            '_links' => [
                'self' => ['href' => "/{$type}/{$id}"],
            ],
            $type => array_merge(['id' => $id], $attributes),
        ];

        foreach ($links as $rel => $href) {
            $data['_links'][$rel] = is_array($href) ? $href : ['href' => $href];
        }

        if (!empty($embedded)) {
            $data['_embedded'] = $embedded;
        }

        return new JsonResponse($data, 200, [
            'Content-Type' => 'application/hal+json',
        ]);
    }

    public static function collection(
        string $type,
        array $items,
        array $meta = [],
        array $links = []
    ): JsonResponse {
        $data = [
            '_links' => [
                'self' => ['href' => "/{$type}"],
            ],
            '_embedded' => [
                $type => $items,
            ],
        ];

        if (!empty($meta)) {
            $data['_meta'] = $meta;
        }

        foreach ($links as $rel => $href) {
            $data['_links'][$rel] = is_array($href) ? $href : ['href' => $href];
        }

        return new JsonResponse($data, 200, [
            'Content-Type' => 'application/hal+json',
        ]);
    }

    public static function created(
        string $type,
        string $id,
        array $attributes,
        array $links = []
    ): JsonResponse {
        $response = self::resource($type, $id, $attributes, $links);
        return $response->withStatus(201);
    }

    public static function noContent(): JsonResponse
    {
        return new JsonResponse(null, 204);
    }

    public static function error(
        string $title,
        int $status,
        string $detail = '',
        array $extra = []
    ): JsonResponse {
        $data = [
            '_error' => [
                'status' => $status,
                'title' => $title,
            ],
        ];

        if ($detail) {
            $data['_error']['detail'] = $detail;
        }

        $data['_error'] = array_merge($data['_error'], $extra);

        return new JsonResponse($data, $status, [
            'Content-Type' => 'application/hal+json',
        ]);
    }

    public static function badRequest(string $detail = ''): JsonResponse
    {
        return self::error('Bad Request', 400, $detail);
    }

    public static function notFound(string $detail = ''): JsonResponse
    {
        return self::error('Not Found', 404, $detail);
    }

    public static function unprocessableEntity(array $errors): JsonResponse
    {
        return self::error('Unprocessable Entity', 422, 'Validation failed', [
            'errors' => $errors,
        ]);
    }

    public static function unauthorized(string $detail = 'Unauthorized'): JsonResponse
    {
        return self::error('Unauthorized', 401, $detail);
    }

    public static function forbidden(string $detail = 'Forbidden'): JsonResponse
    {
        return self::error('Forbidden', 403, $detail);
    }
}
