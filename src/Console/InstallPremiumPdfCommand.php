<?php

namespace AppUncles\PremiumPdf\Console;

use Illuminate\Console\Command;

class InstallPremiumPdfCommand extends Command
{
    protected $signature = 'premium-pdf:install
        {--npm : Install puppeteer-core only. Recommended and fast. Does not download browser.}
        {--browser : Install full puppeteer with bundled browser download. Use when no Chrome/Edge/Chromium/Brave is installed.}';

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

        if ($this->option('browser')) {
            $this->warn('Installing full Puppeteer with bundled browser.');
            $this->warn('This may take time because Puppeteer downloads a browser.');

            $exitCode = $this->runShellCommand('npm install puppeteer');

            if ($exitCode !== 0) {
                $this->error('npm install puppeteer failed.');
                return self::FAILURE;
            }

            $this->info('Full Puppeteer installed successfully.');
        } elseif ($this->option('npm')) {
            $this->info('Installing puppeteer-core...');
            $this->line('This is fast and does not download Chrome.');

            $exitCode = $this->runShellCommand('npm install puppeteer-core');

            if ($exitCode !== 0) {
                $this->error('npm install puppeteer-core failed.');
                return self::FAILURE;
            }

            $this->info('puppeteer-core installed successfully.');
        } else {
            $this->warn('Skipped npm dependency installation.');
            $this->line('Recommended command: php artisan premium-pdf:install --npm');
            $this->line('If no browser is installed: php artisan premium-pdf:install --browser');
        }

        $this->newLine();

        $this->info('Browser support:');
        $this->line('- Google Chrome');
        $this->line('- Microsoft Edge');
        $this->line('- Chromium');
        $this->line('- Brave Browser');

        $this->newLine();

        $this->info('Auto-detection is enabled.');
        $this->line('If auto-detection fails, add browser path in .env:');

        $this->newLine();

        $this->line('Windows Chrome:');
        $this->line('PREMIUM_PDF_BROWSER_PATH="C:/Program Files/Google/Chrome/Application/chrome.exe"');

        $this->newLine();

        $this->line('Windows Edge:');
        $this->line('PREMIUM_PDF_BROWSER_PATH="C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe"');

        $this->newLine();

        $this->line('Linux Chrome:');
        $this->line('PREMIUM_PDF_BROWSER_PATH=/usr/bin/google-chrome');

        $this->newLine();

        $this->line('Linux Chromium:');
        $this->line('PREMIUM_PDF_BROWSER_PATH=/usr/bin/chromium-browser');

        $this->newLine();

        $this->info('AppUncles Premium PDF installed successfully.');

        return self::SUCCESS;
    }

    protected function runShellCommand(string $command): int
    {
        passthru($command, $exitCode);

        return (int) $exitCode;
    }
}