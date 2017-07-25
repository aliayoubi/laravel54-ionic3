<?php

namespace CodeFlix\Providers;

use CodeFlix\Models\Video;
use Dingo\Api\Exception\Handler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\ServiceProvider;
use Laravel\Dusk\DuskServiceProvider;
use Tymon\JWTAuth\Exceptions\JWTException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Video::updated(function ($video){

            if(!$video->completed) {
                if($video->file != null && $video->thumb != null && $video->duration != null){
                    $video->completed = true;
                    $video->save();
                }
            }

        });
    }
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment() !== 'prod') {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
            $this->app->register(DuskServiceProvider::class);
        }

        $this->app->bind('bootstrapper::form', function ($app){
            $form = new Form(
                $app->make('collective::html'),
                $app->make('url'),
                $app->make('view'),
                $app['session.store']->token()
            );

            return $form->setSessionStore($app['session.store']);
        });

        $handler = app(Handler::class);
        $handler->register(function (AuthenticationException $exception){
            return response()->json(['error' => 'Unauthenticated'], 401);
        });
        $handler->register(function (JWTException $exception){
            return response()->json(['error' => $exception->getMessage()], 401);
        });
    }
}
