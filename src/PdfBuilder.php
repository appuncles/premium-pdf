<?php

namespace AppUncles\PremiumPdf;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;
use RuntimeException;

class PdfBuilder
{
    protected ?string $url = null;
    protected ?string $html = null;
    protected string $name = 'document.pdf';
    protected string $format;
    protected bool $landscape;
    protected bool $printBackground;
    protected bool $preferCssPageSize;
    protected string $mediaType;
    protected string $waitUntil;
    protected int $timeout;
    protected array $margin;

    public function __construct()
    {
        $this->format = config('premium-pdf.format', 'A4');
        $this->landscape = (bool) config('premium-pdf.landscape', false);
        $this->printBackground = (bool) config('premium-pdf.print_background', true);
        $this->preferCssPageSize = (bool) config('premium-pdf.prefer_css_page_size', true);
        $this->mediaType = config('premium-pdf.media_type', 'screen');
        $this->waitUntil = config('premium-pdf.wait_until', 'networkidle0');
        $this->timeout = (int) config('premium-pdf.timeout', 120000);
        $this->margin = config('premium-pdf.margin');
    }

    public function url(string $url): self
    {
        $this->url = $url;
        $this->html = null;

        return $this;
    }

    public function html(string $html): self
    {
        $this->html = $html;
        $this->url = null;

        return $this;
    }

    public function loadHtml(string $html): self
    {
        return $this->html($html);
    }

    public function loadView(string $view, array $data = []): self
    {
        $this->html = View::make($view, $data)->render();
        $this->url = null;

        return $this;
    }

    public function name(string $name): self
    {
        $this->name = str_ends_with($name, '.pdf') ? $name : $name.'.pdf';

        return $this;
    }

    public function format(string $format): self
    {
        $this->format = $format;

        return $this;
    }

    public function landscape(bool $value = true): self
    {
        $this->landscape = $value;

        return $this;
    }

    public function portrait(): self
    {
        $this->landscape = false;

        return $this;
    }

    public function margin(string $top, string $right, string $bottom, string $left): self
    {
        $this->margin = compact('top', 'right', 'bottom', 'left');

        return $this;
    }

    public function output(): string
    {
        $paths = $this->preparePaths();

        $payload = [
            'url' => $this->url,
            'html' => $this->html,
            'output' => $paths['pdf'],
            'pdf' => [
                'format' => $this->format,
                'landscape' => $this->landscape,
                'printBackground' => $this->printBackground,
                'preferCSSPageSize' => $this->preferCssPageSize,
                'margin' => $this->margin,
            ],
            'browser' => [
                'executablePath' => config('premium-pdf.chrome_path'),
                'args' => config('premium-pdf.browser_args', []),
            ],
            'page' => [
                'mediaType' => $this->mediaType,
                'waitUntil' => $this->waitUntil,
                'timeout' => $this->timeout,
            ],
        ];

        file_put_contents($paths['json'], json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->runRenderer($paths['json']);

        if (! file_exists($paths['pdf'])) {
            $this->cleanup($paths);
            throw new RuntimeException('PDF file was not generated.');
        }

        $pdf = file_get_contents($paths['pdf']);

        $this->cleanup($paths);

        return $pdf;
    }

    public function stream(?string $filename = null)
    {
        $filename = $filename ?: $this->name;

        return Response::make($this->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
        ]);
    }

    public function download(?string $filename = null)
    {
        $filename = $filename ?: $this->name;

        return Response::make($this->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function save(string $path): string
    {
        $directory = dirname($path);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents($path, $this->output());

        return $path;
    }

    protected function preparePaths(): array
    {
        $tempPath = config('premium-pdf.temp_path', storage_path('app/premium-pdf'));

        if (! is_dir($tempPath)) {
            mkdir($tempPath, 0755, true);
        }

        $id = uniqid('premium_pdf_', true);

        return [
            'json' => $tempPath.'/'.$id.'.json',
            'pdf' => $tempPath.'/'.$id.'.pdf',
        ];
    }

    protected function runRenderer(string $jsonPath): void
    {
        $node = config('premium-pdf.node_binary', 'node');
        $script = config('premium-pdf.renderer_script');

        if (! file_exists($script)) {
            throw new RuntimeException('Premium PDF renderer script not found: '.$script);
        }

        $command = [$node, $script, $jsonPath];

        $descriptorSpec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open($command, $descriptorSpec, $pipes, base_path());

        if (! is_resource($process)) {
            throw new RuntimeException('Unable to start Premium PDF renderer.');
        }

        fclose($pipes[0]);

        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);

        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        if ($exitCode !== 0) {
            throw new RuntimeException("Premium PDF renderer failed.\n\nSTDOUT:\n{$stdout}\n\nSTDERR:\n{$stderr}");
        }
    }

    protected function cleanup(array $paths): void
    {
        foreach ($paths as $path) {
            if (is_file($path)) {
                @unlink($path);
            }
        }
    }
}