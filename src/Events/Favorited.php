<?php

namespace Dealskoo\Favorite\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class Favorited extends Event
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
}
