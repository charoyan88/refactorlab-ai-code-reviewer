<?php

namespace RefactorLab\AICodeReviewer;

use Illuminate\Support\ServiceProvider;
use RefactorLab\AICodeReviewer\AIReviewService;
use RefactorLab\AICodeReviewer\GitHubCommenter;

class AIServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Merge package config
        $this->mergeConfigFrom(
            __DIR__.'/../config/aicode.php', 'aicode'
        );

        // Register services
        $this->app->singleton(AIReviewService::class, function ($app) {
            return new AIReviewService(config('aicode.openai'));
        });
        
        $this->app->singleton(GitHubCommenter::class, function ($app) {
            return new GitHubCommenter(config('aicode.github'));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Load routes
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        
        // Publish config
        $this->publishes([
            __DIR__.'/../config/aicode.php' => config_path('aicode.php'),
        ], 'aicode-config');
    }
} 