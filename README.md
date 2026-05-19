# Atik PDF

Enterprise-grade hybrid Laravel PDF package with ultra-fast large-table PDF generation and full Bangla font support.

## Overview

`atik-pdf` uses a hybrid engine architecture to solve the classic memory-exhaustion problem when generating huge PDFs in PHP:

1. **PHP Engine (`mPDF`)**: Automatically used for styled HTML documents, invoices, and reports under a configured threshold (default 5,000 rows).
2. **Python Engine (`FastAPI + ReportLab`)**: Automatically used for huge tables, streaming PDFs, and memory-optimized generation for datasets up to 100k+ rows.

## Features

- **Hybrid Architecture**: Best of both worlds (PHP for styles, Python for massive data).
- **Automatic Engine Switching**: `AtikPdf::auto()` handles the switching for you.
- **Queue & Async Support**: Generate massive PDFs in the background.
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

### 2. Run the Python Microservice

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

### Standard View (mPDF)

Perfect for invoices or certificates.

```php
use Atik\Pdf\Facades\AtikPdf;

return AtikPdf::view('invoices.standard', ['invoice' => $data])
    ->download('invoice_001.pdf');
```

### Automatic Engine (Table)

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

### Async / Background Generation

For 50k+ rows, queue it!

```php
AtikPdf::async($massiveRowArray, $columns, 'Massive Dataset')
    ->webhook('https://myapp.com/api/webhooks/pdf-ready')
    ->queue('reports/massive_report_august.pdf');

return response()->json(['message' => 'PDF is generating in the background!']);
```

## Configuration

See `config/atik-pdf.php` to configure:
- `auto_threshold`: Number of rows to trigger the Python engine.
- `python_engine.api_url`: URL of your deployed FastAPI service.
- Queues, disks, and mPDF margins.
# atik-pdf
