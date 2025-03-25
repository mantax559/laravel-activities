<?php

namespace Mantax559\LaravelActivities\Services;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mantax559\LaravelActivities\Enums\ActivityEventEnum;
use Mantax559\LaravelActivities\Models\Activity;
use UnitEnum;

class ActivityService
{
    public static function log(ActivityEventEnum $event, Model $model): ?Activity
    {
        $table = $model->getTable();
        $model = $model->withoutRelations();

        switch (true) {
            case cmprenum($event, ActivityEventEnum::Create):
                $newData = $model->toArray();
                break;

            case cmprenum($event, ActivityEventEnum::Update):
                $oldData = $model->getOriginal();
                $newData = $model->toArray();
                break;

            case cmprenum($event, ActivityEventEnum::Delete):
                $oldData = $model->getOriginal();
                break;

            default:
                throw new Exception("The activity event enum value \"{$event->value}\" does not exist!");
        }

        $activityData = [
            'table' => $table,
            'record_id' => $model->id,
            'event' => $event,
            'old_data' => $oldData ?? null,
            'new_data' => $newData ?? null,
        ];

        $activityData = self::filterChangedData($model, $activityData, 'old_data', 'new_data');

        if (empty($activityData['old_data']) && empty($activityData['new_data'])) {
            return null;
        }

        self::appendUserContext($activityData);

        return Activity::create($activityData);
    }

    public static function truncateHtml(array|string|null $text): string
    {
        if (empty($text) && ! cmprstr($text, 0)) {
            return '-';
        }

        if (is_array($text)) {
            $text = json_encode($text);
        }

        $text = strip_tags($text);
        $maxLength = Activity::DATA_VALUE_MAX_LENGTH;

        return mb_strlen($text) > $maxLength
            ? mb_substr($text, 0, $maxLength).'...'
            : $text;
    }

    private static function filterChangedData(Model $model, array $data, ?string $oldDataKey = null, ?string $newDataKey = null): array
    {
        $data[$oldDataKey] = self::removeUnnecessaryColumns($model, $data[$oldDataKey]);
        $data[$newDataKey] = self::removeUnnecessaryColumns($model, $data[$newDataKey]);

        if (empty($data[$oldDataKey]) || empty($data[$newDataKey])) {
            return $data;
        }

        $columnTypes = self::getModelColumnDetails($model);

        foreach ($data[$oldDataKey] as $column => $oldValue) {
            $newValue = $data[$newDataKey][$column];

            $oldValue = $oldValue instanceof UnitEnum ? $oldValue->value : $oldValue;
            $newValue = $newValue instanceof UnitEnum ? $newValue->value : $newValue;

            $type = $columnTypes[$column]['type'] ?? null;
            if (in_array($type, [Activity::COLUMN_TYPE_BIGINT, Activity::COLUMN_TYPE_TINYINT, Activity::COLUMN_TYPE_INT])) {
                $data[$oldDataKey][$column] = (int) $oldValue;
                $data[$newDataKey][$column] = (int) $newValue;
            } elseif (in_array($type, [Activity::COLUMN_TYPE_DECIMAL])) {
                $data[$oldDataKey][$column] = round($oldValue, $columnTypes[$column]['scale']);
                $data[$newDataKey][$column] = round($newValue, $columnTypes[$column]['scale']);
            } elseif (in_array($type, [Activity::COLUMN_TYPE_DATE, Activity::COLUMN_TYPE_DATETIME, Activity::COLUMN_TYPE_TIMESTAMP])) {
                $data[$oldDataKey][$column] = Carbon::parse($oldValue, config('app.timezone'))->setTimezone(config('app.timezone'))->toIso8601String();
                $data[$newDataKey][$column] = Carbon::parse($newValue, config('app.timezone'))->setTimezone(config('app.timezone'))->toIso8601String();
            } else {
                $data[$oldDataKey][$column] = $oldValue;
                $data[$newDataKey][$column] = $newValue;
            }

            if (cmprstr($data[$oldDataKey][$column], $data[$newDataKey][$column])) {
                unset($data[$oldDataKey][$column], $data[$newDataKey][$column]);
            }
        }

        asort($data[$oldDataKey]);
        asort($data[$newDataKey]);

        return $data;
    }

    private static function appendUserContext(array &$activityData): void
    {
        if (auth()->check()) {
            $activityData['user_id'] = auth()->id();
            $activityData['ip_address'] = request()->ip();
            $activityData['user_agent'] = request()->userAgent();
            $activityData['locale'] = request()->getLocale();
        }
    }

    private static function removeUnnecessaryColumns(Model $model, ?array $data): ?array
    {
        $columnsToExclude = array_merge($model->getHidden(), Activity::EXCEPT_COLUMNS);

        return array_diff_key($data ?? [], array_flip($columnsToExclude));
    }

    private static function getModelColumnDetails(Model $model): array
    {
        $table = $model->getTable();
        $cacheKey = "{$table}_column_details";

        return Cache::rememberForever($cacheKey, function () use ($table) {
            return collect(Schema::getColumnListing($table))
                ->mapWithKeys(function ($column) use ($table) {
                    $columnType = DB::getSchemaBuilder()->getColumnType($table, $column);

                    $columnDetails = DB::select("SHOW COLUMNS FROM {$table} WHERE Field = ?", [$column])[0];
                    $typeWithLength = $columnDetails->Type;

                    $length = null;
                    $precision = null;
                    $scale = null;

                    if (preg_match('/decimal\((\d+),(\d+)\)/', $typeWithLength, $matches)) {
                        $precision = isset($matches[1]) ? (int) $matches[1] : null;
                        $scale = isset($matches[2]) ? (int) $matches[2] : null;
                    } elseif (preg_match('/\((\d+)\)/', $typeWithLength, $matches)) {
                        $length = isset($matches[1]) ? (int) $matches[1] : null;
                    }

                    return [$column => [
                        'type' => $columnType,
                        'length' => $length,
                        'precision' => $precision,
                        'scale' => $scale,
                    ]];
                })->toArray();
        });
    }
}
