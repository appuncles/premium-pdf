<?php

namespace AppUncles\PremiumPdf\Facades;

use Illuminate\Support\Facades\Facade;

class PremiumPdf extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'premium-pdf';
    }
}