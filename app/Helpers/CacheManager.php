<?php

namespace App\Helpers;

use App\Enums\CacheName;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;

class CacheManager
{
    public string $key;

    public function __construct(CacheName $cacheName)
    {
        $this->key = $cacheName->value;
    }

    /**
     *  Make the cache name more unique by appending any string
     *
     *  @param string|null $string
     *  @return CacheManager
     */
    public function append($string, $uppercase = false)
    {
        if(!empty($string)) {

            /**
             *  Sometimes we may need to uppercase the string provided e.g
             *
             *  $string = 'Hello World'
             *
             *  should be converted into:
             *
             *  $string = 'HELLO_WORLD'
             *
             *  However other strings must not be converted e.g a bearer token string
             *
             *  $string = 1|rNaH9JZmO1iqZQgrU0upu55GYpOjc0MoPLZb8eI0981ae9ca;
             *
             *  We can use $uppercase=true/false to determine the outcome
             */
            if($uppercase) {

                $string = strtoupper(Str::snake($string));

            }

            $this->key .= ':'.$string;

        }

        return $this;
    }

    /**
     *  Make the cache name more unique by appending the specified model class name and id
     *
     *  @param Model $model
     *  @return CacheManager
     */
    public function appendModel(Model $model)
    {
        $modelId = $model->id;
        $modelName = Str::upper(Str::snake(class_basename($model)));

        $this->append($modelName);  //  USER, STORE, FRIEND_GROUP, e.t.c
        $this->append($modelId);    //  1, 2, 3, e.t.c
        return $this;
    }

    /**
     *  Make the cache name more unique by appending the request bearer token
     *
     *  @return CacheManager
     */
    public function appendBearerToken()
    {
        $this->append(request()->bearerToken());    //  1|O5nzCM8diEF2vKXnCCBMXZwMaEpKZREW15qZjoaS089a25e6
        return $this;
    }



    /**
     *  @param \Closure|\DateTimeInterface|\DateInterval|int|null $ttl
     *  @param \Closure $callback
     *  @return mixed
     */
    public function remember($ttl, $callback)
    {
        return Cache::remember($this->key, $ttl, $callback);
    }

    /**
     *  @param \Closure $callback
     *  @return mixed
     */
    public function rememberForever($callback)
    {
        return Cache::rememberForever($this->key, $callback);
    }

    /**
     *  @param mixed $value
     *  @param \Closure|\DateTimeInterface|\DateInterval|int|null $ttl
     *  @return mixed
     */
    public function put($value, $ttl)
    {
        return Cache::put($this->key, $value, $ttl);
    }

    /**
     *  @return mixed
     */
    public function get()
    {
        return Cache::get($this->key);
    }

    /**
     *  @return bool
     */
    public function has()
    {
        return Cache::has($this->key);
    }

    /**
     *  @return bool
     */
    public function forget()
    {
        return Cache::forget($this->key);
    }

    /**
     *  @return bool
     */
    public function increment($value = 1)
    {
        return Cache::increment($this->key, $value);
    }

    /**
     *  @return int|bool
     */
    public function decrement($value = 1)
    {
        return Cache::decrement($this->key, $value);
    }
}
