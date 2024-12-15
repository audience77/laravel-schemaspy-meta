<?php

namespace Audience77\LaravelSchemaspyMeta\Tests\Models\BelongsToMany;

use Illuminate\Database\Eloquent\Model;

class Flight extends Model
{
    public function pilots()
    {
        return $this->belongsToMany(Pilot::class, 'flight_pilot');
    }
}
