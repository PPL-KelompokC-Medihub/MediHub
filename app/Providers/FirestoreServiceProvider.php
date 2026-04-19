<?php

namespace App\Providers;

use App\Auth\FirestoreUserProvider;
use App\Services\FirestoreService;
use App\Services\MedihubFirestoreRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class FirestoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(FirestoreService::class, function () {
            return new FirestoreService();
        });
    }

    public function boot(): void
    {
        Auth::provider('firestore', function ($app) {
            return new FirestoreUserProvider(
                $app->make(FirestoreService::class),
                $app->make(MedihubFirestoreRepository::class),
            );
        });
    }
}
