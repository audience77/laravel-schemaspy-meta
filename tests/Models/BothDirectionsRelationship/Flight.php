<?php

namespace Audience77\LaravelSchemaspyMeta\Tests\Models\BothDirectionsRelationship;

use Illuminate\Database\Eloquent\Model;

class Flight extends Model
{
    public function plane()
    {
        return $this->belongsTo(Plane::class);
    }
}
