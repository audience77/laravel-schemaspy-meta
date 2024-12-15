<?php

namespace Audience77\LaravelSchemaspyMeta\Tests\Models\HasMany;

use Illuminate\Database\Eloquent\Model;

class Plane extends Model
{
    public function flights()
    {
        return $this->hasMany(Flight::class);
    }

    // for test coverage
    public function notRelationMethod()
    {
        return 'notRelationMethod';
    }
}
