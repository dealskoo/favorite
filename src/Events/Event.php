<?php

namespace Dealskoo\Favorite\Events;

use Illuminate\Database\Eloquent\Model;

class Event
{
    public $favorite;

    public function __construct(Model $favorite)
    {
        $this->favorite = $favorite;
    }
}
