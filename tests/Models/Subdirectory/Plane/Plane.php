<?php

namespace Audience77\LaravelSchemaspyMeta\Tests\Models\Subdirectory\Plane;

use Illuminate\Database\Eloquent\Model;
use Audience77\LaravelSchemaspyMeta\Tests\Models\Subdirectory\Flight\Flight;

class Plane extends Model
{
    public function flights()
    {
        return $this->hasMany(Flight::class);
    }
}
