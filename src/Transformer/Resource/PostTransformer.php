<?php
declare(strict_types=1);

namespace App\Transformer\Resource;

use App\Entity\Post;
use League\Fractal\TransformerAbstract;

/**
 * Transforms a Post entity for API output.
 */
class PostTransformer extends TransformerAbstract
{
    protected $availableIncludes = ['author'];

    /**
     * Transform the post entity.
     *
     * @param Post $post
     * @return array
     */
    public function transform(Post $post): array
    {
        return [
            'id' => $post->getId(),
            'title' => $post->getTitle(),
            'content' => $post->getContent(),
            'created_at' => $post->getCreatedAt()->format('c'),
            'updated_at' => $post->getUpdatedAt() ? $post->getUpdatedAt()->format('c') : null,
        ];
    }

    /**
     * Include author.
     *
     * @param Post $post
     * @return \League\Fractal\Resource\Item
     */
    public function includeAuthor(Post $post)
    {
        return $this->item($post->getAuthor(), new \App\Transformer\Resource\UserTransformer());
    }
}