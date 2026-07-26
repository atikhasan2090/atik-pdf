<?php

namespace Atik\\PdfExcel\\Engines;

use Atik\\PdfExcel\\Contracts\PdfEngineInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Exception;

class PythonEngine implements PdfEngineInterface
{
    protected array $data = [];
    protected string $type = 'table'; // table or view
    
    public function fromView(string $view, array $data = []): self
    {
        // Python engine might not be ideal for complex blade views unless it uses something like WeasyPrint
        // For now, this throws or falls back, but let's assume Python handles basic HTML or raw data.
        throw new Exception("PythonEngine is currently optimized for large tables. Use PhpEngine for views.");
    }

    public function fromTable(array|\Iterator $rows, array $columns = [], string $title = ''): self
    {
        // For extremely large datasets, we might want to store it in a temp JSON file
        // and pass the URL/path to Python, but for moderate-large (5k-50k), HTTP JSON might work.
        // For massive (100k+), we should stream it to a file first.
        
        // Convert iterator to array if necessary, or better yet, write to a json lines file
        $rowsArray = is_array($rows) ? $rows : iterator_to_array($rows);
        
        $this->data = [
            'title' => $title,
            'columns' => $columns,
            'rows' => $rowsArray,
        ];
        
        $this->type = 'table';
        
        return $this;
    }

    public function stream(string $filename = 'document.pdf')
    {
        return response($this->output())
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $filename . '"');
    }

    public function download(string $filename = 'document.pdf')
    {
        return response($this->output())
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function save(string $path, string $disk = null): bool
    {
        $disk = $disk ?? config('laravel-pdf-excel.queue.disk', 'local');
        return Storage::disk($disk)->put($path, $this->output());
    }

    public function output(): string
    {
        $url = rtrim(config('laravel-pdf-excel.python_engine.api_url'), '/') . '/generate-pdf';
        
        $response = Http::timeout(config('laravel-pdf-excel.python_engine.timeout', 300))
            ->post($url, $this->data);

        if ($response->successful()) {
            return $response->body();
        }

        throw new Exception("Python PDF generation failed: " . $response->body());
    }

    public function getContentType(): string
    {
        return 'application/pdf';
    }

    public function getExtension(): string
    {
        return 'pdf';
    }
}
