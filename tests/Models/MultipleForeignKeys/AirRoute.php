<?php

namespace Audience77\LaravelSchemaspyMeta\Tests\Models\MultipleForeignKeys;

use Illuminate\Database\Eloquent\Model;

class AirRoute extends Model
{
    public function flight()
    {
        return $this->hasMany(Flight::class);
    }
}
