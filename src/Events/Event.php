<?php

namespace Dealskoo\Favorite\Events;

use Dealskoo\Favorite\Models\Favorite;

class Event
{
    public $favorite;

    public function __construct(Favorite $favorite)
    {
        $this->favorite = $favorite;
    }
}
