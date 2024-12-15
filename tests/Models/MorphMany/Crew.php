<?php

namespace Audience77\LaravelSchemaspyMeta\Tests\Models\MorphMany;

use Illuminate\Database\Eloquent\Model;

class Crew extends Model
{
    public function boardingLogs()
    {
        return $this->morphMany(BoardingLog::class, 'boarding_loggable');
    }
}
