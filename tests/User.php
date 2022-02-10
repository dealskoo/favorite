<?php

namespace Dealskoo\Favorite\Tests;

use Dealskoo\Favorite\Traits\Favoriter;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use Favoriter;

    protected $fillable = ['name'];
}
