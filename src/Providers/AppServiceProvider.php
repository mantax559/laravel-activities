<?php

namespace Mantax559\LaravelActivities\Providers;

use Illuminate\Support\ServiceProvider;
use Mantax559\LaravelActivities\Models\Activity;
use Mantax559\LaravelHelpers\Helpers\TableHelper;

class AppServiceProvider extends ServiceProvider
{
    private const PATH_CONFIG = __DIR__.'/../../config/laravel-activities.php';

    private const PATH_MIGRATIONS = __DIR__.'/../../database/migrations';

    public function boot(): void
    {
        Activity::resolveRelationUsing(config('laravel-activities.relationship_name'), function ($orderModel) {
            return $orderModel->belongsTo(
                config('laravel-activities.user_model'),
                TableHelper::getForeignKey(config('laravel-activities.user_model'))
            );
        });

        $this->publishes([
            self::PATH_CONFIG => config_path('laravel-activities.php'),
        ], 'config');

        $this->loadMigrationsFrom(self::PATH_MIGRATIONS);
    }

    public function register(): void
    {
        $this->mergeConfigFrom(self::PATH_CONFIG, 'laravel-activities');
    }
}
