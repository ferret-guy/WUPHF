<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use App\Providers\DynamoUserProvider;
use Illuminate\Support\ServiceProvider;

class DynamoAuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        Auth::provider('dynamo', function ($app, array $config) {
            // Return an instance of Illuminate\Contracts\Auth\UserProvider...

            return new DynamoUserProvider($app->make('aws')->createClient("DynamoDb"), $app->make('hash'), $config['table']);
        });
    }
}
