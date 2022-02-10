<?php

namespace Dealskoo\Favorite\Traits;

use Dealskoo\Favorite\Models\Favorite;
use Illuminate\Database\Eloquent\Model;

trait Favoriteable
{
    public function isFavoritedBy(Model $user)
    {
        return $this->hasBeenFavoritedBy($user);
    }

    public function hasFavoriter(Model $user)
    {
        return $this->hasBeenFavoritedBy($user);
    }

    public function hasBeenFavoritedBy(Model $user)
    {
        if (is_a($user, config('auth.providers.users.model'))) {
            if ($this->relationLoaded('favoriters')) {
                return $this->favoriters->contains($user);
            }
            $favorites = $this->relationLoaded('favorites') ? $this->favorites : $this->favoriters();
            return $favorites->where('user_id', $user->getKey())->count() > 0;
        }
        return false;
    }

    public function favorites()
    {
        return $this->morphMany(Favorite::class, 'favoriteable');
    }

    public function favoriters()
    {
        return $this->belongsToMany(
            config('auth.providers.users.model'),
            'favorites',
            'favoriteable_id',
            'user_id')
            ->where('favoriteable_type', $this->getMorphClass());
    }
}
