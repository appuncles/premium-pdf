<?php

namespace AppUncles\PremiumPdf;

class PremiumPdf
{
    public function url(string $url): PdfBuilder
    {
        return (new PdfBuilder())->url($url);
    }

    public function html(string $html): PdfBuilder
    {
        return (new PdfBuilder())->html($html);
    }

    public function loadHtml(string $html): PdfBuilder
    {
        return $this->html($html);
    }

    public function loadView(string $view, array $data = []): PdfBuilder
    {
        return (new PdfBuilder())->loadView($view, $data);
    }
}