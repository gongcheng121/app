<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(\App\Repositories\BabyInfoRepository::class, \App\Repositories\BabyInfoRepositoryEloquent::class);
        $this->app->bind(\App\Repositories\BabyInfoRepository::class, \App\Repositories\BabyInfoRepositoryEloquent::class);
        $this->app->bind(\App\Repositories\BabyVoteRepository::class, \App\Repositories\BabyVoteRepositoryEloquent::class);
        $this->app->bind(\App\Repositories\BabyVotePollRepository::class, \App\Repositories\BabyVotePollRepositoryEloquent::class);
        $this->app->bind(\App\Repositories\LiveRepository::class, \App\Repositories\LiveRepositoryEloquent::class);
        $this->app->bind(\App\Repositories\LiveHostdRepository::class, \App\Repositories\LiveHostdRepositoryEloquent::class);
        $this->app->bind(\App\Repositories\DriftBottleRepository::class, \App\Repositories\DriftBottleRepositoryEloquent::class);
        $this->app->bind(\App\Repositories\BankVoteRepository::class, \App\Repositories\BankVoteRepositoryEloquent::class);
        $this->app->bind(\App\Repositories\BankVotePollRepository::class, \App\Repositories\BankVotePollRepositoryEloquent::class);
        $this->app->bind(\App\Repositories\BankVotePollLogRepository::class, \App\Repositories\BankVotePollLogRepositoryEloquent::class);
        $this->app->bind(\App\Repositories\HongFuRepository::class, \App\Repositories\HongFuRepositoryEloquent::class);
        //:end-bindings:
    }
}
