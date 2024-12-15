<?php

namespace Audience77\LaravelSchemaspyMeta\Tests\Models\MorphedByMany;

use Illuminate\Database\Eloquent\Model;

class Flight extends Model
{
    public function passengers()
    {
        return $this->morphedByMany(Passenger::class, 'flightable');
    }

    public function cargos()
    {
        return $this->morphedByMany(Cargo::class, 'flightable');
    }
}
