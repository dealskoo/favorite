<?php

namespace Dealskoo\Favorite\Events;

use Dealskoo\Favorite\Models\Favorite;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class Event
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $favorite;

    public function __construct(Favorite $favorite)
    {
        $this->favorite = $favorite;
    }
}
