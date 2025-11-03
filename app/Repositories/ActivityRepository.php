<?php

namespace App\Repositories;

use App\Models\Activity;

class ActivityRepository
{
    /**
     * Get activity ID with all its children IDs recursively.
     */
    public function getActivityWithChildren(int $activityId): array
    {
        $ids = [$activityId];
        $children = Activity::where('parent_id', $activityId)->get();

        foreach ($children as $child) {
            $ids = array_merge($ids, $this->getActivityWithChildren($child->id));
        }

        return $ids;
    }
}
