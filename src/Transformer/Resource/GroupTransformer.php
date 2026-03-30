<?php

declare(strict_types=1);

namespace App\Transformer\Resource;

use App\Entity\Group;
use League\Fractal\TransformerAbstract;

/**
 * Transforms a Group entity for API output.
 */
class GroupTransformer extends TransformerAbstract
{
    protected $availableIncludes = ['users'];

    /**
     * Transform the group entity.
     *
     * @param Group $group
     * @return array
     */
    public function transform(Group $group): array
    {
        return [
            'id' => $group->getId(),
            'name' => $group->getName(),
            'created_at' => $group->getCreatedAt()->format('c'),
        ];
    }

    /**
     * Include users.
     *
     * @param Group $group
     * @return \League\Fractal\Resource\Collection
     */
    public function includeUsers(Group $group)
    {
        return $this->collection($group->getUsers(), new \App\Transformer\Resource\UserTransformer());
    }
}
