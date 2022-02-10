<?php

namespace Dealskoo\Favorite\Tests\Feature;

use Closure;
use Dealskoo\Favorite\Events\Favorited;
use Dealskoo\Favorite\Events\Unfavorited;
use Dealskoo\Favorite\Tests\Post;
use Dealskoo\Favorite\Tests\Product;
use Dealskoo\Favorite\Tests\TestCase;
use Dealskoo\Favorite\Tests\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    public function test_basic_features()
    {
        Event::fake();
        $user = User::create(['name' => 'user']);
        $post = Post::create(['title' => 'test guide']);
        $user->favorite($post);
        Event::assertDispatched(Favorited::class, function ($event) use ($user, $post) {
            $favorite = $event->favorite;
            return $favorite->favoriteable instanceof Post && $favorite->user instanceof User && $favorite->user->id == $user->id && $favorite->favoriteable->id == $post->id;
        });

        $this->assertTrue($user->hasFavorited($post));
        $this->assertTrue($post->isFavoritedBy($user));

        $user->unfavorite($post);
        Event::assertDispatched(Unfavorited::class, function ($event) use ($user, $post) {
            $favorite = $event->favorite;
            return $favorite->favoriteable instanceof Post && $favorite->user instanceof User && $favorite->user->id == $user->id && $favorite->favoriteable->id == $post->id;
        });
    }

    public function test_unfavorite_features()
    {
        $user1 = User::create(['name' => 'user1']);
        $user2 = User::create(['name' => 'user2']);
        $user3 = User::create(['name' => 'user3']);
        $post = Post::create(['title' => 'test post']);

        $user1->favorite($post);
        $user1->favorite($post);
        $user2->favorite($post);
        $user3->favorite($post);

        $user2->unfavorite($post);
        $this->assertFalse($user2->hasFavorited($post));
        $this->assertTrue($user1->hasFavorited($post));
        $this->assertTrue($user3->hasFavorited($post));
        $this->assertCount(1, $user1->favorites);
    }

    public function test_aggregations()
    {
        $user = User::create(['name' => 'user']);

        $post1 = Post::create(['title' => 'post1']);
        $post2 = Post::create(['title' => 'post2']);

        $product1 = Product::create(['name' => 'product1']);
        $product2 = Product::create(['name' => 'product2']);

        $user->favorite($post1);
        $user->favorite($post2);
        $user->favorite($product1);
        $user->favorite($product2);

        $this->assertCount(4, $user->favorites);
        $this->assertCount(2, $user->favorites()->withType(Post::class)->get());
    }

    public function test_object_favoriters()
    {
        $user1 = User::create(['name' => 'user1']);
        $user2 = User::create(['name' => 'user2']);
        $user3 = User::create(['name' => 'user3']);

        $post = Post::create(['title' => 'test post']);

        $user1->favorite($post);
        $user2->favorite($post);
        $this->assertCount(2, $post->favoriters);

        $this->assertSame($user1->name, $post->favoriters[0]['name']);
        $this->assertSame($user2->name, $post->favoriters[1]['name']);

        $sqls = $this->getQueryLog(function () use ($post, $user1, $user2, $user3) {
            $this->assertTrue($post->isFavoritedBy($user1));
            $this->assertTrue($post->isFavoritedBy($user2));
            $this->assertFalse($post->isFavoritedBy($user3));
        });

        $this->assertEmpty($sqls->all());
    }

    public function test_eager_loading()
    {
        $user = User::create(['name' => 'user']);

        $post1 = Post::create(['title' => 'post1']);
        $post2 = Post::create(['title' => 'post2']);

        $product1 = Product::create(['name' => 'product1']);
        $product2 = Product::create(['name' => 'product2']);

        $user->favorite($post1);
        $user->favorite($post2);
        $user->favorite($product1);
        $user->favorite($product2);

        $sqls = $this->getQueryLog(function () use ($user) {
            $user->load('favorites.favoriteable');
        });

        $this->assertCount(3, $sqls);

        $sqls = $this->getQueryLog(function () use ($user, $post1) {
            $user->hasFavorited($post1);
        });

        $this->assertEmpty($sqls->all());
    }

    public function test_eager_loading_error()
    {
        $user = User::create(['name' => 'user']);

        $post1 = Post::create(['title' => 'post1']);
        $post2 = Post::create(['title' => 'post2']);

        $user->favorite($post2);

        $this->assertFalse($user->hasFavorited($post1));
        $this->assertTrue($user->hasFavorited($post2));

        $user->load('favorites');

        $this->assertFalse($user->hasFavorited($post1));
        $this->assertTrue($user->hasFavorited($post2));

        $user1 = User::create(['name' => 'user1']);
        $user2 = User::create(['name' => 'user2']);

        $post = Post::create(['title' => 'Hello world!']);

        $user2->favorite($post);

        $this->assertFalse($post->isFavoritedBy($user1));
        $this->assertTrue($post->isFavoritedBy($user2));

        $post->load('favorites');

        $this->assertFalse($post->isFavoritedBy($user1));
        $this->assertTrue($post->isFavoritedBy($user2));
    }

    public function test_has_favorited()
    {
        $user = User::create(['name' => 'user']);
        $post = Post::create(['title' => 'post']);

        $user->favorite($post);
        $user->favorite($post);
        $user->favorite($post);
        $user->favorite($post);

        $this->assertTrue($user->hasFavorited($post));
        $this->assertTrue($post->hasBeenFavoritedBy($user));
        $this->assertDatabaseCount('favorites', 1);

        $user->unfavorite($post);
        $this->assertFalse($user->hasFavorited($post));
        $this->assertFalse($post->hasBeenFavoritedBy($user));
        $this->assertDatabaseCount('favorites', 0);
    }

    protected function getQueryLog(Closure $callback)
    {
        $sqls = collect([]);
        DB::listen(function ($query) use ($sqls) {
            $sqls->push(['sql' => $query->sql, 'bindings' => $query->bindings]);
        });
        $callback();
        return $sqls;
    }
}
