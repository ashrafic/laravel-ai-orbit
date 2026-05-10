<?php

namespace Ashrafic\AiOrbit;

use Ashrafic\AiOrbit\Contracts\AgentRegistryContract;
use Ashrafic\AiOrbit\Contracts\FeatureGate;
use Ashrafic\AiOrbit\Http\Livewire\AgentInspector;
use Ashrafic\AiOrbit\Http\Livewire\AgentSandbox;
use Ashrafic\AiOrbit\Http\Livewire\MessageTimeline;
use Ashrafic\AiOrbit\Http\Livewire\ThreadExplorer;
use Ashrafic\AiOrbit\Http\Livewire\TodayStats;
use Ashrafic\AiOrbit\Http\Middleware\Authorize;
use Ashrafic\AiOrbit\Services\AgentRegistry;
use Ashrafic\AiOrbit\Support\FreeFeatureGate;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class OrbitServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/ai-orbit.php', 'ai-orbit'
        );

        $this->app->bind(FeatureGate::class, FreeFeatureGate::class);

        $this->app->singleton(Orbit::class);

        $this->app->singleton(
            AgentRegistryContract::class,
            AgentRegistry::class
        );
    }

    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        $this->loadRoutes();
        $this->loadViews();
        $this->defineGate();
        $this->registerPublishables();
        $this->registerLivewireComponents();
    }

    /**
     * Register the package routes.
     */
    protected function loadRoutes(): void
    {
        Route::group($this->routeConfiguration(), function (): void {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        });
    }

    /**
     * Get the route configuration.
     *
     * @return array{domain: string|null, prefix: string, middleware: array<int, string>}
     */
    protected function routeConfiguration(): array
    {
        return [
            'domain' => config('ai-orbit.domain'),
            'prefix' => config('ai-orbit.path', 'ai-orbit'),
            'middleware' => array_merge(
                config('ai-orbit.middleware', ['web']),
                [Authorize::class]
            ),
        ];
    }

    /**
     * Load the package views.
     */
    protected function loadViews(): void
    {
        $this->loadViewsFrom(
            __DIR__.'/../resources/views', 'ai-orbit'
        );
    }

    /**
     * Define the access Gate for the Orbit dashboard.
     */
    protected function defineGate(): void
    {
        Gate::define('viewAiOrbit', function ($user = null) {
            return $this->app->environment('local');
        });
    }

    /**
     * Register publishable resources.
     */
    protected function registerPublishables(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/ai-orbit.php' => config_path('ai-orbit.php'),
        ], 'ai-orbit-config');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/laravel-ai-orbit'),
        ], 'ai-orbit-views');

        $this->publishes([
            __DIR__.'/../dist' => public_path('vendor/ai-orbit'),
        ], 'ai-orbit-assets');
    }

    /**
     * Register the package Livewire components.
     */
    protected function registerLivewireComponents(): void
    {
        Livewire::component('ai-orbit.today-stats', TodayStats::class);
        Livewire::component('ai-orbit.thread-explorer', ThreadExplorer::class);
        Livewire::component('ai-orbit.message-timeline', MessageTimeline::class);
        Livewire::component('ai-orbit.agent-sandbox', AgentSandbox::class);
        Livewire::component('ai-orbit.agent-inspector', AgentInspector::class);
    }
}
