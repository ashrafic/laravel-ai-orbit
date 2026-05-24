<?php

namespace Ashrafic\AiOrbit\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class InstallCommand extends Command
{
    protected $signature = 'ai-orbit:install';

    protected $description = 'Install Laravel AI Orbit and publish its resources';

    public function handle(): int
    {
        $this->comment('Publishing AI Orbit configuration...');
        Artisan::call('vendor:publish', ['--tag' => 'ai-orbit-config'], $this->output);

        $this->comment('Publishing AI Orbit assets...');
        Artisan::call('vendor:publish', ['--tag' => 'ai-orbit-assets'], $this->output);

        $this->comment('Publishing AI Orbit migrations...');
        Artisan::call('vendor:publish', ['--tag' => 'ai-orbit-migrations'], $this->output);

        $this->info('AI Orbit installed successfully.');

        $this->newLine();
        $this->line('Next, run the migrations to create the tables needed to store Orbit data:');
        $this->comment('  php artisan migrate');

        return self::SUCCESS;
    }
}
