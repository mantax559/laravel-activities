<?php

namespace Mantax559\LaravelActivities\Enums;

use Mantax559\LaravelHelpers\Traits\EnumTrait;

enum ActivityEventEnum: string
{
    use EnumTrait;

    case Create = 'create';
    case Update = 'update';
    case Delete = 'delete';
}
