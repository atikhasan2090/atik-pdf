<?php

namespace Atik\Pdf;

use Illuminate\Contracts\Foundation\Application;
use Atik\Pdf\Engines\PhpEngine;
use Atik\Pdf\Engines\PythonEngine;
use Atik\Pdf\Jobs\GenerateLargePdfJob;
use Atik\Pdf\Contracts\PdfEngineInterface;
use Illuminate\Support\Str;

class AtikPdfManager
{
    protected Application $app;
    
    protected ?PdfEngineInterface $engine = null;
    
    // Stored data for async/fluent calls
    protected array $currentData = [];
    protected string $currentType = '';
    protected ?string $webhookUrl = null;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Automatically choose engine based on row count
     */
    public function auto(array $rows, array $columns = [], string $title = ''): self
    {
        $threshold = config('atik-pdf.auto_threshold', 5000);
        $count = is_array($rows) ? count($rows) : iterator_count($rows);
        
        if ($count >= $threshold) {
            $this->engine = new PythonEngine();
        } else {
            $this->engine = new PhpEngine();
        }

        $this->engine->fromTable($rows, $columns, $title);
        
        return $this;
    }

    /**
     * Use PHP engine (mPDF) specifically for views
     */
    public function view(string $view, array $data = []): self
    {
        $this->engine = new PhpEngine();
        $this->engine->fromView($view, $data);
        return $this;
    }

    /**
     * Use PHP engine for a table
     */
    public function table(array|\Iterator $rows, array $columns = [], string $title = ''): self
    {
        $this->engine = new PhpEngine();
        $this->engine->fromTable($rows, $columns, $title);
        return $this;
    }

    /**
     * Explicitly use Python engine for large tables
     */
    public function largeTable(array|\Iterator $rows, array $columns = [], string $title = ''): self
    {
        $this->engine = new PythonEngine();
        $this->engine->fromTable($rows, $columns, $title);
        return $this;
    }

    /**
     * Prepare data for an async job
     */
    public function async(array $rows, array $columns = [], string $title = ''): self
    {
        $this->currentData = [
            'rows' => $rows,
            'columns' => $columns,
            'title' => $title
        ];
        $this->currentType = 'async';
        
        return $this;
    }

    /**
     * Set a webhook URL for async jobs
     */
    public function webhook(string $url): self
    {
        $this->webhookUrl = $url;
        return $this;
    }

    /**
     * Dispatch the async job
     */
    public function queue(string $filename = null)
    {
        if (empty($this->currentData)) {
            throw new \Exception("No data provided for queue. Call async() or largeTable() first.");
        }

        $filename = $filename ?? 'document_' . time() . '_' . Str::random(5) . '.pdf';
        $path = config('atik-pdf.queue.path', 'pdfs/') . $filename;
        $disk = config('atik-pdf.queue.disk', 'local');

        GenerateLargePdfJob::dispatch(
            $this->currentData, 
            $path, 
            $disk, 
            $this->webhookUrl
        )->onQueue(config('atik-pdf.queue.queue', 'default'));

        return [
            'status' => 'queued',
            'path' => $path,
            'disk' => $disk
        ];
    }

    /**
     * Add watermark
     */
    public function watermark(string $textOrImagePath): self
    {
        // Implementation depends on the underlying engine. 
        // For mPDF, it supports SetWatermarkText / SetWatermarkImage
        if ($this->engine instanceof PhpEngine) {
            // using reflection or adding a method to interface
            // For now, this is a placeholder stub
        }
        return $this;
    }

    /**
     * Stream the PDF to browser
     */
    public function stream(string $filename = 'document.pdf')
    {
        if (!$this->engine) {
            throw new \Exception("Engine not initialized. Call view(), table(), or auto() first.");
        }
        return $this->engine->stream($filename);
    }

    /**
     * Download the PDF
     */
    public function download(string $filename = 'document.pdf')
    {
        if (!$this->engine) {
            throw new \Exception("Engine not initialized. Call view(), table(), or auto() first.");
        }
        return $this->engine->download($filename);
    }

    /**
     * Save the PDF
     */
    public function save(string $path, string $disk = null): bool
    {
        if (!$this->engine) {
            throw new \Exception("Engine not initialized. Call view(), table(), or auto() first.");
        }
        return $this->engine->save($path, $disk);
    }
}
