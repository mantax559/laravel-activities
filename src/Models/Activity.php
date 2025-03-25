<?php

namespace Mantax559\LaravelActivities\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Mantax559\LaravelActivities\Enums\ActivityEventEnum;
use Mantax559\LaravelHelpers\Helpers\TableHelper;

class Activity extends Model
{
    const EXCEPT_COLUMNS = [
        'id',
        'created_at',
        'updated_at',
    ];

    const DATA_VALUE_MAX_LENGTH = 100;

    const COLUMN_TYPE_BIGINT = 'bigint';

    const COLUMN_TYPE_TINYINT = 'tinyint';

    const COLUMN_TYPE_INT = 'int';

    const COLUMN_TYPE_DECIMAL = 'decimal';

    const COLUMN_TYPE_DATE = 'date';

    const COLUMN_TYPE_DATETIME = 'datetime';

    const COLUMN_TYPE_TIMESTAMP = 'timestamp';

    protected $fillable = [
        'table',
        'record_id',
        'event',
        'old_data',
        'new_data',
        'ip_address',
        'user_agent',
        'locale',
    ];

    protected $casts = [
        'event' => ActivityEventEnum::class,
        'old_data' => 'array',
        'new_data' => 'array',
    ];

    public $timestamps = true;

    public function __construct(array $attributes = [])
    {
        $this->fillable[] = TableHelper::getForeignKey(config('laravel-activities.user_model'));

        parent::__construct($attributes);

        $this->setTable(config('laravel-activities.table'));
    }

    protected static function boot()
    {
        parent::boot();

        static::resolveRelationUsing(config('laravel-activities.relationship_name'), function ($model) {
            return $model->belongsTo(
                config('laravel-activities.user_model'),
                TableHelper::getForeignKey(config('laravel-activities.user_model'))
            );
        });
    }

    protected function oldData(): Attribute
    {
        return Attribute::make(
            get: fn (?string $data) => json_decode($data, true),
            set: fn (?array $data) => json_encode($data),
        );
    }

    protected function newData(): Attribute
    {
        return Attribute::make(
            get: fn (?string $data) => json_decode($data, true),
            set: fn (?array $data) => json_encode($data),
        );
    }

    protected function locale(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => format_string($value, 4),
            set: fn (?string $value) => format_string($value, 3),
        );
    }
}
