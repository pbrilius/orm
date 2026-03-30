<?php
declare(strict_types=1);

namespace App\Transformer\Resource;

use App\Entity\User;
use League\Fractal\TransformerAbstract;

/**
 * Transforms a User entity for API output.
 */
class UserTransformer extends TransformerAbstract
{
    protected $availableIncludes = ['posts', 'group'];

    /**
     * Transform the user entity.
     *
     * @param User $user
     * @return array
     */
    public function transform(User $user): array
    {
        return [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
            'created_at' => $user->getCreatedAt()->format('c'),
            'updated_at' => $user->getUpdatedAt() ? $user->getUpdatedAt()->format('c') : null,
        ];
    }

    /**
     * Include posts.
     *
     * @param User $user
     * @return \League\Fractal\Resource\Collection
     */
    public function includePosts(User $user)
    {
        return $this->collection($user->getPosts(), new \App\Transformer\Resource\PostTransformer());
    }

    /**
     * Include group.
     *
     * @param User $user
     * @return \League\Fractal\Resource\Item
     */
    public function includeGroup(User $user)
    {
        return $this->item($user->getGroup(), new \App\Transformer\Resource\GroupTransformer());
    }
}