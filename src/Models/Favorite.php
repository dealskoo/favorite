<?php

namespace Dealskoo\Favorite\Models;

use Dealskoo\Favorite\Events\Favorited;
use Dealskoo\Favorite\Events\Unfavorited;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    use HasFactory;

    protected $dispatchesEvents = [
        'created' => Favorited::class,
        'deleted' => Unfavorited::class
    ];

    public function favoriteable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'user_id');
    }

    public function favoriter()
    {
        return $this->user();
    }

    public function scopeWithType(Builder $builder, string $type)
    {
        return $builder->where('favoriteable_type', app($type)->getMorphClass());
    }
}
