<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\LogsActivityDefaults;
use Illuminate\Database\Eloquent\Model;

abstract class BaseModel extends Model
{
    use LogsActivityDefaults;
}
