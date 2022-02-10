<?php

namespace Dealskoo\Favorite\Tests;

use Dealskoo\Favorite\Traits\Favoriteable;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use Favoriteable;

    protected $fillable = ['title'];
}
