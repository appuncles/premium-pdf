<?php

namespace AppUncles\PremiumPdf\Console;

use Illuminate\Console\Command;

class InstallPremiumPdfCommand extends Command
{
    protected $signature = 'premium-pdf:install {--npm : Install Puppeteer using npm}';

    protected $description = 'Install AppUncles Premium PDF config and optional Node renderer dependencies.';

    public function handle(): int
    {
        $this->info('Installing AppUncles Premium PDF...');

        $this->call('vendor:publish', [
            '--tag' => 'premium-pdf-config',
            '--force' => true,
        ]);

        $tempPath = config('premium-pdf.temp_path', storage_path('app/premium-pdf'));

        if (! is_dir($tempPath)) {
            mkdir($tempPath, 0755, true);
        }

        $this->info('Temp folder ready: '.$tempPath);

        if ($this->option('npm')) {
            $this->info('Installing Puppeteer...');
            passthru('npm install puppeteer', $exitCode);

            if ($exitCode !== 0) {
                $this->error('npm install puppeteer failed.');
                return self::FAILURE;
            }

            $this->info('Puppeteer installed successfully.');
        } else {
            $this->warn('Skipped npm install.');
            $this->line('Run manually: npm install puppeteer');
        }

        $this->info('AppUncles Premium PDF installed successfully.');

        return self::SUCCESS;
    }
}