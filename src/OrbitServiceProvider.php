<?php

namespace Ashrafic\AiOrbit;

use Ashrafic\AiOrbit\Contracts\AgentRegistryContract;
use Ashrafic\AiOrbit\Http\Livewire\AgentInspector;
use Ashrafic\AiOrbit\Http\Livewire\AgentSandbox;
use Ashrafic\AiOrbit\Http\Livewire\AuditDashboard;
use Ashrafic\AiOrbit\Http\Livewire\BudgetAlerts;
use Ashrafic\AiOrbit\Http\Livewire\CostDashboard;
use Ashrafic\AiOrbit\Http\Livewire\MessageTimeline;
use Ashrafic\AiOrbit\Http\Livewire\PricingMatrix;
use Ashrafic\AiOrbit\Http\Livewire\PromptLab;
use Ashrafic\AiOrbit\Http\Livewire\PromptLibrary;
use Ashrafic\AiOrbit\Http\Livewire\ProviderHealth;
use Ashrafic\AiOrbit\Http\Livewire\ThreadExplorer;
use Ashrafic\AiOrbit\Http\Livewire\TodayStats;
use Ashrafic\AiOrbit\Http\Middleware\Authorize;
use Ashrafic\AiOrbit\Services\AgentRegistry;
use Ashrafic\AiOrbit\Services\AiRunRecorder;
use Ashrafic\AiOrbit\Services\BudgetMonitor;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Ai\Events\AddingFileToStore;
use Laravel\Ai\Events\AgentFailedOver;
use Laravel\Ai\Events\AgentPrompted;
use Laravel\Ai\Events\AgentStreamed;
use Laravel\Ai\Events\AudioGenerated;
use Laravel\Ai\Events\CreatingStore;
use Laravel\Ai\Events\EmbeddingsGenerated;
use Laravel\Ai\Events\FileAddedToStore;
use Laravel\Ai\Events\FileDeleted;
use Laravel\Ai\Events\FileRemovedFromStore;
use Laravel\Ai\Events\FileStored;
use Laravel\Ai\Events\GeneratingAudio;
use Laravel\Ai\Events\GeneratingEmbeddings;
use Laravel\Ai\Events\GeneratingImage;
use Laravel\Ai\Events\GeneratingTranscription;
use Laravel\Ai\Events\ImageGenerated;
use Laravel\Ai\Events\InvokingTool;
use Laravel\Ai\Events\PromptingAgent;
use Laravel\Ai\Events\ProviderFailedOver;
use Laravel\Ai\Events\RemovingFileFromStore;
use Laravel\Ai\Events\Reranked;
use Laravel\Ai\Events\Reranking;
use Laravel\Ai\Events\StoreCreated;
use Laravel\Ai\Events\StoreDeleted;
use Laravel\Ai\Events\StoringFile;
use Laravel\Ai\Events\StreamingAgent;
use Laravel\Ai\Events\ToolInvoked;
use Laravel\Ai\Events\TranscriptionGenerated;
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
        $this->loadMigrations();
        $this->defineGate();
        $this->registerPublishables();
        $this->registerLivewireComponents();
        $this->registerAiEventListeners();
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
     * Load the package database migrations.
     */
    protected function loadMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
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
        Livewire::component('ai-orbit.prompt-lab', PromptLab::class);
        Livewire::component('ai-orbit.audit-dashboard', AuditDashboard::class);
        Livewire::component('ai-orbit.cost-dashboard', CostDashboard::class);
        Livewire::component('ai-orbit.pricing-matrix', PricingMatrix::class);
        Livewire::component('ai-orbit.budget-alerts', BudgetAlerts::class);
        Livewire::component('ai-orbit.provider-health', ProviderHealth::class);
        Livewire::component('ai-orbit.prompt-library', PromptLibrary::class);
    }

    /**
     * Register Laravel AI SDK observability listeners.
     */
    protected function registerAiEventListeners(): void
    {
        if (! config('ai-orbit.observability.enabled', true)) {
            return;
        }

        if (! config('ai-orbit.observability.store_runs', true) && ! config('ai-orbit.budget.enabled', true)) {
            return;
        }

        $events = $this->app->make(Dispatcher::class);

        $startingEvents = [
            PromptingAgent::class => 'agent_text',
            StreamingAgent::class => 'agent_stream',
            GeneratingImage::class => 'image',
            GeneratingAudio::class => 'audio',
            GeneratingTranscription::class => 'transcription',
            GeneratingEmbeddings::class => 'embeddings',
            Reranking::class => 'reranking',
            StoringFile::class => 'file',
            CreatingStore::class => 'store',
            AddingFileToStore::class => 'store_file',
            RemovingFileFromStore::class => 'store_file',
        ];

        foreach ($startingEvents as $event => $operation) {
            $events->listen($event, function (object $event) use ($operation): void {
                $this->app->make(AiRunRecorder::class)->recordStarting($event, $operation);
            });
        }

        $completedEvents = [
            AgentPrompted::class => 'agent_text',
            AgentStreamed::class => 'agent_stream',
            ImageGenerated::class => 'image',
            AudioGenerated::class => 'audio',
            TranscriptionGenerated::class => 'transcription',
            EmbeddingsGenerated::class => 'embeddings',
            Reranked::class => 'reranking',
            FileStored::class => 'file',
            FileDeleted::class => 'file',
            StoreCreated::class => 'store',
            StoreDeleted::class => 'store',
            FileAddedToStore::class => 'store_file',
            FileRemovedFromStore::class => 'store_file',
        ];

        foreach ($completedEvents as $event => $operation) {
            $events->listen($event, function (object $event) use ($operation): void {
                $this->app->make(AiRunRecorder::class)->recordCompleted($event, $operation);
                $this->app->make(BudgetMonitor::class)->checkCompletedEvent($event);
            });
        }

        $events->listen(InvokingTool::class, function (InvokingTool $event): void {
            $this->app->make(AiRunRecorder::class)->recordToolEvent($event);
        });

        $events->listen(ToolInvoked::class, function (ToolInvoked $event): void {
            $this->app->make(AiRunRecorder::class)->recordToolEvent($event);
        });

        $events->listen(ProviderFailedOver::class, function (ProviderFailedOver $event): void {
            $this->app->make(AiRunRecorder::class)->recordFailover($event);
        });

        $events->listen(AgentFailedOver::class, function (AgentFailedOver $event): void {
            $this->app->make(AiRunRecorder::class)->recordFailover($event);
        });
    }
}
