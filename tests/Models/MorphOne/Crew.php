<?php

namespace Audience77\LaravelSchemaspyMeta\Tests\Models\MorphOne;

use Illuminate\Database\Eloquent\Model;

class Crew extends Model
{
    public function contact()
    {
        return $this->morphOne(Contact::class, 'contactable');
    }
}
