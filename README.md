# AppUncles Premium PDF

Full Tailwind supported premium PDF generator for Laravel.

AppUncles Premium PDF gives Laravel developers a DomPDF-like API, but instead of old CSS rendering, it uses real Chrome through Puppeteer. Because of that, modern CSS, Tailwind CSS, flexbox, grid, gradients, shadows, custom fonts, images, and premium layouts render properly.

## Features

- Laravel Composer package
- DomPDF-like simple API
- Full Tailwind CSS support
- Modern CSS support
- Flexbox and CSS Grid support
- Gradient, shadow, border radius, and background support
- Blade view to PDF
- URL to PDF
- HTML to PDF
- Stream PDF in browser
- Download PDF
- Save PDF to storage
- Custom file name
- A4 and other paper formats
- Portrait and landscape support
- Custom margins
- Laravel config publish support
- Works with Laravel Vite assets using URL mode
- Built for invoices, quotations, receipts, reports, certificates, ID cards, admission forms, and business PDFs

## Requirements

- PHP 8.2 or higher
- Laravel 10, 11, 12, or 13
- Node.js
- npm
- Puppeteer / Chrome
- A server where Node commands are allowed

## Important Note

For full Tailwind support, use URL mode:

```php
PremiumPdf::url(route('invoice.preview'))->download('invoice.pdf');
```

URL mode is best because Chrome opens your actual Laravel route, so `@vite`, Tailwind, images, fonts, public assets, and CDN files load normally.

`loadView()` is available, but it is better for simple HTML. For premium Tailwind PDF, URL mode is recommended.

## Installation

### Method 1: Install from Packagist

Use this when the package is published on Packagist:

```bash
composer require appuncles/premium-pdf
php artisan premium-pdf:install --npm
```

The install command will:

- Publish the config file
- Create the temp folder
- Install Puppeteer using npm

### Method 2: Install directly from GitHub

Use this method if the package is not yet available on Packagist.

Add this to your Laravel project's `composer.json`:

```json
"repositories": [
  {
    "type": "vcs",
    "url": "https://github.com/appuncles/premium-pdf"
  }
]
```

Then run:

```bash
composer require appuncles/premium-pdf:dev-main
php artisan premium-pdf:install --npm
```

### Method 3: Install using GitHub release version

If a release tag is available, for example `v1.0.0`, use:

```bash
composer require appuncles/premium-pdf:^1.0
php artisan premium-pdf:install --npm
```

## Publish Config

If you only want to publish the config manually:

```bash
php artisan vendor:publish --tag=premium-pdf-config
```

After publishing, the config file will be available at:

```txt
config/premium-pdf.php
```

## Environment Variables

You can configure the package using `.env`:

```env
PREMIUM_PDF_NODE_BINARY=node
PREMIUM_PDF_CHROME_PATH=
PREMIUM_PDF_FORMAT=A4
PREMIUM_PDF_LANDSCAPE=false
PREMIUM_PDF_PRINT_BACKGROUND=true
PREMIUM_PDF_PREFER_CSS_PAGE_SIZE=true
PREMIUM_PDF_MEDIA_TYPE=screen
PREMIUM_PDF_WAIT_UNTIL=networkidle0
PREMIUM_PDF_TIMEOUT=120000
PREMIUM_PDF_MARGIN_TOP=10mm
PREMIUM_PDF_MARGIN_RIGHT=10mm
PREMIUM_PDF_MARGIN_BOTTOM=10mm
PREMIUM_PDF_MARGIN_LEFT=10mm
```

For Ubuntu server with system Chrome:

```env
PREMIUM_PDF_CHROME_PATH=/usr/bin/google-chrome
```

For Chromium:

```env
PREMIUM_PDF_CHROME_PATH=/usr/bin/chromium-browser
```

If you keep `PREMIUM_PDF_CHROME_PATH` empty, Puppeteer will use its own installed browser.

## Basic Usage

Import the facade:

```php
use AppUncles\PremiumPdf\Facades\PremiumPdf;
```

### Stream PDF in browser

```php
return PremiumPdf::url(route('invoice.preview'))
    ->name('invoice.pdf')
    ->stream();
```

### Download PDF

```php
return PremiumPdf::url(route('invoice.preview'))
    ->name('invoice.pdf')
    ->download();
```

### Save PDF to storage

```php
PremiumPdf::url(route('invoice.preview'))
    ->save(storage_path('app/invoice.pdf'));
```

## Complete Route Example

Add this to `routes/web.php`:

```php
use AppUncles\PremiumPdf\Facades\PremiumPdf;
use Illuminate\Support\Facades\Route;

Route::get('/invoice/preview', function () {
    return view('pdf.invoice', [
        'invoice' => [
            'number' => 'INV-1001',
            'customer' => 'AppUncles Client',
            'email' => 'client@example.com',
            'amount' => 15000,
        ],
    ]);
})->name('invoice.preview');

Route::get('/invoice/view', function () {
    return PremiumPdf::url(route('invoice.preview'))
        ->name('invoice.pdf')
        ->stream();
})->name('invoice.view');

Route::get('/invoice/download', function () {
    return PremiumPdf::url(route('invoice.preview'))
        ->name('invoice.pdf')
        ->download();
})->name('invoice.download');
```

Now open these URLs:

```txt
/invoice/preview
/invoice/view
/invoice/download
```

## Blade PDF Example with Tailwind

Create this file:

```txt
resources/views/pdf/invoice.blade.php
```

Paste this code:

```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice PDF</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @page {
            size: A4;
            margin: 10mm;
        }

        html,
        body {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 antialiased">
    <main class="mx-auto max-w-4xl rounded-[32px] bg-white p-10 shadow-2xl ring-1 ring-slate-200">
        <section class="flex items-start justify-between border-b border-slate-200 pb-8">
            <div>
                <div class="inline-flex rounded-full bg-emerald-50 px-4 py-2 text-xs font-bold uppercase tracking-widest text-emerald-700 ring-1 ring-emerald-100">
                    Paid Invoice
                </div>

                <h1 class="mt-5 text-5xl font-black tracking-tight text-slate-950">
                    Invoice
                </h1>

                <p class="mt-2 text-sm text-slate-500">
                    Generated using AppUncles Premium PDF
                </p>
            </div>

            <div class="rounded-3xl bg-gradient-to-br from-slate-950 to-slate-700 px-7 py-6 text-right text-white shadow-xl">
                <p class="text-xs uppercase tracking-[0.3em] text-slate-300">Invoice No</p>
                <p class="mt-2 text-2xl font-black">#{{ $invoice['number'] }}</p>
            </div>
        </section>

        <section class="mt-10 grid grid-cols-2 gap-6">
            <div class="rounded-3xl bg-slate-50 p-6 ring-1 ring-slate-200">
                <p class="text-xs font-black uppercase tracking-widest text-slate-400">Bill To</p>
                <h2 class="mt-3 text-xl font-extrabold text-slate-950">
                    {{ $invoice['customer'] }}
                </h2>
                <p class="mt-2 text-sm text-slate-500">
                    {{ $invoice['email'] }}
                </p>
            </div>

            <div class="rounded-3xl bg-slate-50 p-6 ring-1 ring-slate-200">
                <p class="text-xs font-black uppercase tracking-widest text-slate-400">Date</p>
                <h2 class="mt-3 text-xl font-extrabold text-slate-950">
                    {{ now()->format('d M Y') }}
                </h2>
                <p class="mt-2 text-sm text-slate-500">
                    Payment Status: Completed
                </p>
            </div>
        </section>

        <section class="mt-10 overflow-hidden rounded-3xl ring-1 ring-slate-200">
            <table class="w-full text-left">
                <thead class="bg-slate-950 text-white">
                    <tr>
                        <th class="px-6 py-4 text-sm font-bold">Service</th>
                        <th class="px-6 py-4 text-right text-sm font-bold">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <tr>
                        <td class="px-6 py-5">
                            <p class="font-bold text-slate-900">Website Development</p>
                            <p class="mt-1 text-sm text-slate-500">Laravel premium website</p>
                        </td>
                        <td class="px-6 py-5 text-right font-black text-slate-950">
                            ₹10,000
                        </td>
                    </tr>

                    <tr>
                        <td class="px-6 py-5">
                            <p class="font-bold text-slate-900">SEO Setup</p>
                            <p class="mt-1 text-sm text-slate-500">Basic SEO and schema setup</p>
                        </td>
                        <td class="px-6 py-5 text-right font-black text-slate-950">
                            ₹5,000
                        </td>
                    </tr>
                </tbody>
            </table>
        </section>

        <section class="mt-10 flex justify-end">
            <div class="w-80 rounded-3xl bg-gradient-to-br from-emerald-500 to-teal-600 p-7 text-white shadow-2xl">
                <p class="text-sm font-bold uppercase tracking-widest text-emerald-100">
                    Total Amount
                </p>
                <p class="mt-3 text-5xl font-black">
                    ₹{{ number_format($invoice['amount']) }}
                </p>
            </div>
        </section>

        <footer class="mt-10 rounded-3xl bg-slate-950 p-6 text-center text-sm text-slate-300">
            Thank you for your business.
        </footer>
    </main>
</body>
</html>
```

## Available Methods

### `url()`

Generate PDF from a route or full URL.

```php
PremiumPdf::url(route('invoice.preview'))->download();
```

### `loadView()`

Generate PDF from a Blade view.

```php
PremiumPdf::loadView('pdf.invoice', [
    'invoice' => $invoice,
])->download('invoice.pdf');
```

### `html()`

Generate PDF from raw HTML.

```php
PremiumPdf::html('<h1>Hello PDF</h1>')->download('hello.pdf');
```

### `loadHtml()`

Alias of `html()`.

```php
PremiumPdf::loadHtml('<h1>Hello PDF</h1>')->stream();
```

### `name()`

Set default PDF file name.

```php
PremiumPdf::url(route('invoice.preview'))
    ->name('invoice.pdf')
    ->download();
```

### `stream()`

Open PDF in browser.

```php
PremiumPdf::url(route('invoice.preview'))->stream('invoice.pdf');
```

### `download()`

Download PDF.

```php
PremiumPdf::url(route('invoice.preview'))->download('invoice.pdf');
```

### `save()`

Save PDF to a path.

```php
PremiumPdf::url(route('invoice.preview'))
    ->save(storage_path('app/public/invoice.pdf'));
```

### `format()`

Set page format.

```php
PremiumPdf::url(route('invoice.preview'))
    ->format('A4')
    ->download('invoice.pdf');
```

Supported Puppeteer formats include:

```txt
Letter
Legal
Tabloid
Ledger
A0
A1
A2
A3
A4
A5
A6
```

### `landscape()`

Generate landscape PDF.

```php
PremiumPdf::url(route('invoice.preview'))
    ->landscape()
    ->download('invoice.pdf');
```

### `portrait()`

Force portrait mode.

```php
PremiumPdf::url(route('invoice.preview'))
    ->portrait()
    ->download('invoice.pdf');
```

### `margin()`

Set custom margins.

```php
PremiumPdf::url(route('invoice.preview'))
    ->margin('8mm', '8mm', '8mm', '8mm')
    ->download('invoice.pdf');
```

## Custom Page Size

You can use CSS page size in your Blade file:

```css
@page {
  size: A4;
  margin: 10mm;
}
```

For ID card or custom paper:

```css
@page {
  size: 86mm 54mm;
  margin: 0;
}
```

Then generate:

```php
return PremiumPdf::url(route('id-card.preview'))
    ->name('id-card.pdf')
    ->download();
```

## Authenticated PDF Routes

If your PDF preview route is private, use signed routes.

```php
use Illuminate\Support\Facades\URL;

$url = URL::temporarySignedRoute(
    'invoice.preview',
    now()->addMinutes(5),
    ['invoice' => $invoice->id]
);

return PremiumPdf::url($url)
    ->name('invoice.pdf')
    ->download();
```

Signed route example:

```php
Route::get('/invoice/{invoice}/preview', function ($invoice) {
    return view('pdf.invoice', [
        'invoice' => $invoice,
    ]);
})->middleware('signed')->name('invoice.preview');
```

## Using Images

Use absolute URLs or Laravel asset URLs:

```blade
<img src="{{ asset('uploads/logo.png') }}" class="h-16" alt="Logo">
```

For storage images, make sure storage link exists:

```bash
php artisan storage:link
```

Then use:

```blade
<img src="{{ asset('storage/invoices/logo.png') }}" alt="Logo">
```

## Using Custom Fonts

You can use Google Fonts:

```blade
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
```

Then:

```html
<body style="font-family: 'Inter', sans-serif;"></body>
```

Or use Tailwind font classes if configured in your Laravel project.

## Tailwind and Vite Notes

For local development, make sure Vite is running:

```bash
npm run dev
```

For production, build assets:

```bash
npm run build
```

If the PDF does not show CSS, open your preview route in browser first:

```txt
/invoice/preview
```

If the preview route looks correct but PDF does not, check Node/Puppeteer installation.

## Server Notes

This package needs Node.js and Puppeteer/Chrome.

On VPS or dedicated server, install Node.js and npm, then run:

```bash
php artisan premium-pdf:install --npm
```

On shared hosting, this package may not work if:

- Node.js is disabled
- `proc_open()` is disabled
- shell commands are disabled
- Chrome cannot run
- server does not allow Puppeteer

For shared hosting, use a VPS or a remote PDF rendering server.

## Troubleshooting

### 1. `Premium PDF renderer script not found`

Run:

```bash
php artisan vendor:publish --tag=premium-pdf-config
php artisan config:clear
```

Make sure this file exists:

```txt
vendor/appuncles/premium-pdf/bin/render.mjs
```

### 2. `node is not recognized`

Node.js is not installed or not available in PATH.

Install Node.js, then check:

```bash
node -v
npm -v
```

If Node is installed in a custom path, set it in `.env`:

```env
PREMIUM_PDF_NODE_BINARY=/usr/bin/node
```

On Windows, usually this is enough:

```env
PREMIUM_PDF_NODE_BINARY=node
```

### 3. `Cannot find package puppeteer`

Run:

```bash
npm install puppeteer
```

Or:

```bash
php artisan premium-pdf:install --npm
```

### 4. PDF has no Tailwind CSS

Use URL mode:

```php
PremiumPdf::url(route('invoice.preview'))->download();
```

Also make sure Vite assets are available:

```bash
npm run build
```

Open the preview route in browser and confirm the design is correct.

### 5. Blank PDF

Possible reasons:

- Preview route requires login
- URL is not accessible by Chrome
- Vite dev server is not running
- Assets are blocked
- Route has an error

Use signed route for private PDFs.

### 6. Timeout error

Increase timeout:

```php
PremiumPdf::url(route('invoice.preview'))
    ->timeout(180000)
    ->download('invoice.pdf');
```

Or in `.env`:

```env
PREMIUM_PDF_TIMEOUT=180000
```

### 7. Chrome sandbox error on Linux

The package already includes common browser args:

```php
'--no-sandbox',
'--disable-setuid-sandbox',
'--disable-dev-shm-usage',
'--disable-gpu',
```

If needed, configure system Chrome path:

```env
PREMIUM_PDF_CHROME_PATH=/usr/bin/google-chrome
```

## Development Installation

If you are developing this package locally, add this to your Laravel project's `composer.json`:

```json
"repositories": [
  {
    "type": "path",
    "url": "../premium-pdf"
  }
]
```

Then run:

```bash
composer require appuncles/premium-pdf:@dev
php artisan premium-pdf:install --npm
```

## GitHub Installation for Users

Until the package is published on Packagist, users can install it from GitHub:

```json
"repositories": [
  {
    "type": "vcs",
    "url": "https://github.com/appuncles/premium-pdf"
  }
]
```

Then:

```bash
composer require appuncles/premium-pdf:dev-main
php artisan premium-pdf:install --npm
```

## Publishing to Packagist

To allow users to install directly with:

```bash
composer require appuncles/premium-pdf
```

Submit this repository to Packagist:

```txt
https://github.com/appuncles/premium-pdf
```

After Packagist approval, users can install normally.

## Example Use Cases

- Invoices
- Quotations
- Receipts
- Certificates
- Reports
- School ID cards
- Admission forms
- Payment slips
- Salary slips
- Business proposals
- Project estimates
- Medical reports
- Lab reports
- Order invoices
- Delivery notes

## Security Tips

Do not pass untrusted HTML directly into `html()` or `loadHtml()`.

For private PDFs, use signed routes or temporary routes.

Do not expose preview routes publicly if they contain sensitive data.

## License

MIT License.

## Credits

Developed by AppUncles.
