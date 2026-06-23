<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Node Binary
    |--------------------------------------------------------------------------
    |
    | Usually "node" is enough. On some servers you may need full path:
    | /usr/bin/node
    | C:/Program Files/nodejs/node.exe
    |
    */

    'node_binary' => env('PREMIUM_PDF_NODE_BINARY', 'node'),

    /*
    |--------------------------------------------------------------------------
    | Renderer Script
    |--------------------------------------------------------------------------
    */

    'renderer_script' => env(
        'PREMIUM_PDF_RENDERER_SCRIPT',
        base_path('vendor/appuncles/premium-pdf/bin/render.mjs')
    ),

    /*
    |--------------------------------------------------------------------------
    | Temp Path
    |--------------------------------------------------------------------------
    */

    'temp_path' => env(
        'PREMIUM_PDF_TEMP_PATH',
        storage_path('app/premium-pdf')
    ),

    /*
    |--------------------------------------------------------------------------
    | Browser Path
    |--------------------------------------------------------------------------
    |
    | This package needs a Chromium-based browser.
    |
    | Supported:
    | - Google Chrome
    | - Microsoft Edge
    | - Chromium
    | - Brave
    |
    | Keep empty for auto-detect.
    |
    | Windows examples:
    | PREMIUM_PDF_BROWSER_PATH="C:/Program Files/Google/Chrome/Application/chrome.exe"
    | PREMIUM_PDF_BROWSER_PATH="C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe"
    |
    | Linux examples:
    | PREMIUM_PDF_BROWSER_PATH=/usr/bin/google-chrome
    | PREMIUM_PDF_BROWSER_PATH=/usr/bin/chromium-browser
    |
    | PREMIUM_PDF_CHROME_PATH is kept for backward compatibility.
    |
    */

    'browser_path' => env(
        'PREMIUM_PDF_BROWSER_PATH',
        env('PREMIUM_PDF_CHROME_PATH')
    ),

    /*
    |--------------------------------------------------------------------------
    | Defaults
    |--------------------------------------------------------------------------
    */

    'format' => env('PREMIUM_PDF_FORMAT', 'A4'),

    'landscape' => env('PREMIUM_PDF_LANDSCAPE', false),

    'print_background' => env('PREMIUM_PDF_PRINT_BACKGROUND', true),

    'prefer_css_page_size' => env('PREMIUM_PDF_PREFER_CSS_PAGE_SIZE', true),

    'media_type' => env('PREMIUM_PDF_MEDIA_TYPE', 'screen'),

    'wait_until' => env('PREMIUM_PDF_WAIT_UNTIL', 'networkidle0'),

    'timeout' => env('PREMIUM_PDF_TIMEOUT', 120000),

    'margin' => [
        'top' => env('PREMIUM_PDF_MARGIN_TOP', '10mm'),
        'right' => env('PREMIUM_PDF_MARGIN_RIGHT', '10mm'),
        'bottom' => env('PREMIUM_PDF_MARGIN_BOTTOM', '10mm'),
        'left' => env('PREMIUM_PDF_MARGIN_LEFT', '10mm'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Browser Args
    |--------------------------------------------------------------------------
    */

    'browser_args' => [
        '--no-sandbox',
        '--disable-setuid-sandbox',
        '--disable-dev-shm-usage',
        '--disable-gpu',
    ],
];