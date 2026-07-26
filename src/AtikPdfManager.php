<?php

namespace Atik\Pdf;

use Illuminate\Contracts\Foundation\Application;
use Atik\Pdf\Engines\PhpEngine;
use Atik\Pdf\Engines\PythonEngine;
use Atik\Pdf\Engines\ExcelEngine;
use Atik\Pdf\Engines\CsvEngine;
use Atik\Pdf\Jobs\GenerateLargePdfJob;
use Atik\Pdf\Contracts\DocumentEngineInterface;
use Atik\Pdf\Contracts\PdfEngineInterface;
use Illuminate\Support\Str;

class AtikPdfManager
{
    protected Application $app;

    protected ?DocumentEngineInterface $engine = null;

    protected array $currentData = [];
    protected string $currentType = '';
    protected ?string $webhookUrl = null;
    protected string $format = 'pdf';

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function auto(array $rows, array $columns = [], string $title = ''): self
    {
        $threshold = config('laravel-pdf-excel.auto_threshold', 5000);
        $count = is_array($rows) ? count($rows) : iterator_count($rows);

        if ($this->format === 'pdf' && $count >= $threshold) {
            $this->engine = new PythonEngine();
        } else {
            $this->engine = $this->resolveEngine();
        }

        $this->engine->fromTable($rows, $columns, $title);

        return $this;
    }

    public function browser(): self
    {
        $this->engine = new \Atik\Pdf\Engines\BrowserEngine();
        return $this;
    }

    public function view(string $view, array $data = []): self
    {
        if (!$this->engine) {
            $this->engine = new PhpEngine();
        }

        if ($this->engine instanceof PdfEngineInterface) {
            $this->engine->fromView($view, $data);
        } else {
            throw new \Exception("The selected engine does not support view rendering. Use PDF format for views.");
        }

        return $this;
    }

    public function table(array|\Iterator $rows, array $columns = [], string $title = ''): self
    {
        if (!$this->engine) {
            $this->engine = $this->resolveEngine();
        }
        $this->engine->fromTable($rows, $columns, $title);
        return $this;
    }

    public function largeTable(array|\Iterator $rows, array $columns = [], string $title = ''): self
    {
        $this->engine = new PythonEngine();
        $this->engine->fromTable($rows, $columns, $title);
        return $this;
    }

    public function excel(): self
    {
        $this->format = 'excel';
        $this->engine = new ExcelEngine();
        return $this;
    }

    public function csv(): self
    {
        $this->format = 'csv';
        $this->engine = new CsvEngine();
        return $this;
    }

    public function async(array $rows, array $columns = [], string $title = ''): self
    {
        $this->currentData = [
            'rows' => $rows,
            'columns' => $columns,
            'title' => $title,
            'format' => $this->format,
        ];
        $this->currentType = 'async';

        return $this;
    }

    public function webhook(string $url): self
    {
        $this->webhookUrl = $url;
        return $this;
    }

    public function queue(string $filename = null)
    {
        if (empty($this->currentData)) {
            throw new \Exception("No data provided for queue. Call async() or largeTable() first.");
        }

        $ext = $this->resolveExtension();
        $filename = $filename ?? 'document_' . time() . '_' . Str::random(5) . '.' . $ext;
        $path = config('laravel-pdf-excel.queue.path', 'pdfs/') . $filename;
        $disk = config('laravel-pdf-excel.queue.disk', 'local');

        GenerateLargePdfJob::dispatch(
            $this->currentData,
            $path,
            $disk,
            $this->webhookUrl
        )->onQueue(config('laravel-pdf-excel.queue.queue', 'default'));

        return [
            'status' => 'queued',
            'path' => $path,
            'disk' => $disk,
        ];
    }

    public function watermark(string $textOrImagePath): self
    {
        if ($this->engine instanceof PhpEngine) {
            //
        }
        return $this;
    }

    public function stream(string $filename = null)
    {
        if (!$this->engine) {
            throw new \Exception("Engine not initialized. Call table(), excel(), csv(), or view() first.");
        }

        $filename = $this->formatFilename($filename ?? 'document');
        return $this->engine->stream($filename);
    }

    public function download(string $filename = null)
    {
        if (!$this->engine) {
            throw new \Exception("Engine not initialized. Call table(), excel(), csv(), or view() first.");
        }

        $filename = $this->formatFilename($filename ?? 'document');
        return $this->engine->download($filename);
    }

    public function save(string $path, string $disk = null): bool
    {
        if (!$this->engine) {
            throw new \Exception("Engine not initialized. Call table(), excel(), csv(), or view() first.");
        }
        return $this->engine->save($path, $disk);
    }

    protected function resolveEngine(): DocumentEngineInterface
    {
        return match ($this->format) {
            'excel' => new ExcelEngine(),
            'csv' => new CsvEngine(),
            default => new PhpEngine(),
        };
    }

    protected function resolveExtension(): string
    {
        return match ($this->format) {
            'excel' => 'xlsx',
            'csv' => 'csv',
            default => 'pdf',
        };
    }

    protected function formatFilename(string $filename): string
    {
        $ext = $this->resolveExtension();
        $pathInfo = pathinfo($filename);
        if (($pathInfo['extension'] ?? '') === $ext) {
            return $filename;
        }
        return $pathInfo['filename'] . '.' . $ext;
    }
}
