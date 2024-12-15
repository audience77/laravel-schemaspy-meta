<?php

namespace Audience77\LaravelSchemaspyMeta\Tests\Models\MultipleForeignKeys;

use Illuminate\Database\Eloquent\Model;

class Plane extends Model
{
    public function flights()
    {
        return $this->hasMany(Flight::class);
    }
}
