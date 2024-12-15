<?php

namespace Audience77\LaravelSchemaspyMeta\Tests\Models\MorphToMany;

use Illuminate\Database\Eloquent\Model;

class Passenger extends Model
{
    public function flights()
    {
        return $this->morphToMany(Flight::class, 'flightable');
    }
}
