<?php

namespace Audience77\LaravelSchemaspyMeta\Tests\Models\MorphOne;

use Illuminate\Database\Eloquent\Model;

class Pilot extends Model
{
    public function contact()
    {
        return $this->morphOne(Contact::class, 'contactable');
    }
}
