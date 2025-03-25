<?php

namespace Mantax559\LaravelActivities\Traits;

use Illuminate\Database\Eloquent\Model;
use Mantax559\LaravelActivities\Enums\ActivityEventEnum;
use Mantax559\LaravelActivities\Services\ActivityService;

trait ActivityTrait
{
    public static function bootActivityTrait(): void
    {
        static::created(function (Model $model) {
            ActivityService::log(ActivityEventEnum::Create, $model);
        });

        static::updated(function (Model $model) {
            ActivityService::log(ActivityEventEnum::Update, $model);
        });

        static::deleted(function (Model $model) {
            ActivityService::log(ActivityEventEnum::Delete, $model);
        });
    }
}
