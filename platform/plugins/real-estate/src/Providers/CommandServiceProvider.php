<?php

namespace Botble\RealEstate\Providers;

use Botble\RealEstate\Commands\RenewPropertiesCommand;
use Botble\RealEstate\Commands\ProcessLuckyDrawsCommand;
use Illuminate\Support\ServiceProvider;

class CommandServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                RenewPropertiesCommand::class,
                ProcessLuckyDrawsCommand::class,
            ]);
        }
    }
}
