# Atik PDF

Enterprise-grade hybrid Laravel document generation package supporting **PDF**, **Excel**, and **CSV** output with ultra-fast large-table generation and full Bangla font support.

## Overview

`atik-pdf` uses a hybrid engine architecture to solve the classic memory-exhaustion problem when generating huge documents in PHP:

| Format | Engine | Best For |
|--------|--------|----------|
| **PDF** | `mPDF`, `Browsershot`, `FastAPI + ReportLab` | Styled HTML, invoices, reports, massive tables |
| **Excel** | `PhpSpreadsheet` | .xlsx exports with styled headers and auto-sized columns |
| **CSV** | Native PHP | Lightweight tabular data, data migrations |

## Features

- **Laravel 8+ Compatible**: Works with Laravel 8, 10, 11, and 12.
- **Hybrid PDF Architecture**: Best of both worlds (PHP for styles, Python for massive data).
- **Automatic Engine Switching**: `AtikPdf::auto()` handles the PDF engine switching for you.
- **Excel & CSV Support**: Export tabular data to `.xlsx` or `.csv` with the same fluent API.
- **Queue & Async Support**: Generate massive documents in the background.
- **Bangla Font Support**: Built-in support for Noto Sans Bengali and SolaimanLipi.

## Installation

### 1. Install the Laravel Package

```bash
composer require atik/atik-pdf
```

Publish the configuration and (optionally) the Python service:

```bash
php artisan vendor:publish --tag=atik-pdf-config
php artisan vendor:publish --tag=atik-pdf-python-service
```

### 2. Run the Python Microservice (PDF only)

You can run the Python microservice locally or via Docker.

**Via Docker:**
```bash
cd python-service
docker build -t atik-pdf-python .
docker run -d -p 8000:8000 atik-pdf-python
```

**Via Uvicorn (Local):**
```bash
cd python-service
python -m venv venv
source venv/bin/activate
pip install -r requirements.txt
uvicorn main:app --host 0.0.0.0 --port 8000
```

*Don't forget to put your Bangla `.ttf` fonts in `python-service/fonts/`!*

## Usage Examples

### PDF — Standard View (mPDF)

Perfect for invoices or certificates.

```php
use Atik\Pdf\Facades\AtikPdf;

return AtikPdf::view('invoices.standard', ['invoice' => $data])
    ->download('invoice_001.pdf');
```

### PDF — Automatic Engine (Table)

Automatically switches to Python if rows > 5000.

```php
$columns = ['ID', 'Name', 'Amount'];
$rows = [
    [1, 'Atik', '500'],
    // ... 10,000 more rows
];

return AtikPdf::auto($rows, $columns, 'Monthly Report')
    ->stream();
```

### PDF — Async / Background Generation

For 50k+ rows, queue it!

```php
AtikPdf::async($massiveRowArray, $columns, 'Massive Dataset')
    ->webhook('https://myapp.com/api/webhooks/pdf-ready')
    ->queue('reports/massive_report_august.pdf');

return response()->json(['message' => 'PDF is generating in the background!']);
```

### Excel Export

```php
$columns = ['ID', 'Product', 'Price', 'Stock'];
$rows = [
    [1, 'Widget A', 19.99, 100],
    [2, 'Widget B', 29.99, 50],
];

return AtikPdf::excel()
    ->table($rows, $columns, 'Inventory Report')
    ->download('inventory');
```

### CSV Export

```php
return AtikPdf::csv()
    ->table($rows, $columns)
    ->stream('export');
```

### Async Excel/CSV

```php
AtikPdf::excel()
    ->async($rows, $columns, 'Large Dataset')
    ->queue('exports/dataset.xlsx');
```

## Configuration

See `config/atik-pdf.php` to configure:
- `auto_threshold`: Number of rows to trigger the Python engine.
- `python_engine.api_url`: URL of your deployed FastAPI service.
- `excel_engine.author`: Author metadata for .xlsx files.
- `csv_engine.delimiter`, `csv_engine.include_bom`: CSV formatting options.
- Queues, disks, and mPDF margins.
