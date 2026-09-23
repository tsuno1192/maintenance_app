<?php

namespace App\Providers;

use App\Contracts\FaceRecognitionClient;
use App\Models\Documentation;
use App\Policies\DocumentationPolicy;
use App\Services\FaceRecognition\AwsRekognitionFaceRecognitionClient;
use App\Services\FaceRecognition\FakeFaceRecognitionClient;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(FaceRecognitionClient::class, function () {
            $driver = config('face_recognition.driver', 'fake');

            return match ($driver) {
                'fake' => new FakeFaceRecognitionClient,
                'aws' => new AwsRekognitionFaceRecognitionClient,
                default => throw new InvalidArgumentException("Unknown face recognition driver [{$driver}]"),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Documentation::class, DocumentationPolicy::class);

        Broadcast::routes(['middleware' => ['web', 'auth']]);
    }
}
