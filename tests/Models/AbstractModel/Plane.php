<?php

namespace Audience77\LaravelSchemaspyMeta\Tests\Models\AbstractModel;

class Plane extends AbstractModel
{
    public function flights()
    {
        return $this->hasMany(Flight::class);
    }
}
