<?php

namespace AppUncles\PremiumPdf;

use AppUncles\PremiumPdf\Console\InstallPremiumPdfCommand;
use Illuminate\Support\ServiceProvider;

class PremiumPdfServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/premium-pdf.php',
            'premium-pdf'
        );

        $this->app->singleton('premium-pdf', function () {
            return new PremiumPdf();
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/premium-pdf.php' => config_path('premium-pdf.php'),
        ], 'premium-pdf-config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallPremiumPdfCommand::class,
            ]);
        }
    }
}