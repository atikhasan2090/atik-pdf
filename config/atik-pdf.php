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
