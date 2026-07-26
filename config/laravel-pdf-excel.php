<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Engine Threshold
    |--------------------------------------------------------------------------
    |
    | When using AtikPdf::auto(), this threshold determines the number of rows
    | at which the engine switches from the PHP engine (mPDF) to the
    | Python microservice engine (FastAPI + ReportLab).
    |
    */
    'auto_threshold' => 5000,

    /*
    |--------------------------------------------------------------------------
    | PHP Engine (mPDF) Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration specific to the mPDF engine used for smaller documents
    | and styled invoices.
    |
    */
    'php_engine' => [
        'mode' => 'utf-8',
        'format' => 'A4',
        'default_font' => 'nikosh', // Or another Bangla font available in mPDF
        'margin_left' => 15,
        'margin_right' => 15,
        'margin_top' => 16,
        'margin_bottom' => 16,
        'margin_header' => 9,
        'margin_footer' => 9,
        'temp_dir' => storage_path('app/temp/mpdf'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Browser Engine (Spatie Browsershot) Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Headless Chrome PDF generation, perfect for Bootstrap
    | and complex CSS layouts.
    |
    */
    'browser_engine' => [
        'node_binary' => env('ATIK_PDF_NODE_BINARY'),
        'npm_binary' => env('ATIK_PDF_NPM_BINARY'),
        'node_module_path' => env('ATIK_PDF_NODE_MODULE_PATH'),
        'chrome_path' => env('ATIK_PDF_CHROME_PATH'),
        'no_sandbox' => true,
        'format' => 'A4',
        'margins' => [
            'top' => 10,
            'right' => 10,
            'bottom' => 10,
            'left' => 10,
        ],
        'args' => [
            '--disable-gpu',
            '--disable-dev-shm-usage',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Python Microservice Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the Python FastAPI service handling large tables.
    |
    */
    'python_engine' => [
        'api_url' => env('ATIK_PDF_PYTHON_API_URL', 'http://127.0.0.1:8000'),
        'timeout' => 300, // Timeout in seconds for large generations
    ],

    /*
    |--------------------------------------------------------------------------
    | Excel Engine (PhpSpreadsheet) Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Excel (.xlsx) file generation via PhpSpreadsheet.
    |
    */
    'excel_engine' => [
        'author' => env('ATIK_PDF_EXCEL_AUTHOR', 'Atik PDF'),
    ],

    /*
    |--------------------------------------------------------------------------
    | CSV Engine Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for CSV file generation.
    |
    */
    'csv_engine' => [
        'delimiter' => ',',
        'enclosure' => '"',
        'include_bom' => false, // Set to true for Excel-compatible UTF-8 CSV
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for handling asynchronous PDF generation via Laravel Queues.
    |
    */
    'queue' => [
        'connection' => env('QUEUE_CONNECTION', 'redis'),
        'queue' => 'pdf-generation',
        'disk' => env('ATIK_PDF_DISK', 'local'), // Use 's3' for AWS S3
        'path' => 'pdfs/',
    ],
];
