<?php

return [
    'node_binary' => env('PREMIUM_PDF_NODE_BINARY', 'node'),

    'renderer_script' => env(
        'PREMIUM_PDF_RENDERER_SCRIPT',
        base_path('vendor/appuncles/premium-pdf/bin/render.mjs')
    ),

    'temp_path' => env(
        'PREMIUM_PDF_TEMP_PATH',
        storage_path('app/premium-pdf')
    ),

    'chrome_path' => env('PREMIUM_PDF_CHROME_PATH'),

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

    'browser_args' => [
        '--no-sandbox',
        '--disable-setuid-sandbox',
        '--disable-dev-shm-usage',
        '--disable-gpu',
    ],
];