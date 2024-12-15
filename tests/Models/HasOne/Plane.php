<?php

namespace Audience77\LaravelSchemaspyMeta\Tests\Models\HasOne;

use Illuminate\Database\Eloquent\Model;

class Plane extends Model
{
    public function certification()
    {
        return $this->hasOne(Certification::class);
    }
}
