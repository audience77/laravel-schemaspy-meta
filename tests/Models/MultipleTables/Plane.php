<?php

namespace Audience77\LaravelSchemaspyMeta\Tests\Models\MultipleTables;

use Illuminate\Database\Eloquent\Model;

class Plane extends Model
{
    public function flights()
    {
        return $this->hasMany(Flight::class);
    }

    public function certification()
    {
        return $this->hasOne(Certification::class);
    }
}
