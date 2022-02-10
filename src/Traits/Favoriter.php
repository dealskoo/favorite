<?php

namespace Dealskoo\Favorite\Traits;

use Dealskoo\Favorite\Models\Favorite;
use Illuminate\Database\Eloquent\Model;

trait Favoriter
{
    public function favorite(Model $model)
    {
        if (!$this->hasFavorited($model)) {
            $favorite = new Favorite();
            $favorite->user_id = $this->getKey();
            $model->favorites()->save($favorite);
        }
    }

    public function unfavorite(Model $model)
    {
        $favorite = Favorite::query()->where('favoriteable_id', $model->getKey())->where('favoriteable_type', $model->getMorphClass())->where('user_id', $this->getKey())->first();
        if ($favorite) {
            if ($this->relationLoaded('favorites')) {
                $this->unsetRelation('favorites');
            }
            return $favorite->delete();
        }
        return true;
    }

    public function toggleFavorite(Model $model)
    {
        return $this->hasFavorited($model) ? $this->unfavorite($model) : $this->favorite($model);
    }

    public function hasFavorited(Model $model)
    {
        $favorites = $this->relationLoaded('favorites') ? $this->favorites : $this->favorites();
        return $favorites->where('favoriteable_id', $model->getKey())->where('favoriteable_type', $model->getMorphClass())->count() > 0;
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'user_id', $this->getKeyName());
    }

    public function getFavoriteItems(string $model)
    {
        return app($model)->whereHas('favoriters', function ($q) {
            return $q->where('user_id', $this->getKey());
        });
    }
}
