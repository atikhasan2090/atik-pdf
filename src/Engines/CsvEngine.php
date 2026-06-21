<?php

namespace Atik\Pdf\Engines;

use Atik\Pdf\Contracts\DocumentEngineInterface;
use Illuminate\Support\Facades\Storage;

class CsvEngine implements DocumentEngineInterface
{
    protected string $content = '';
    protected string $contentType = 'text/csv';
    protected string $extension = 'csv';
    protected string $delimiter;
    protected string $enclosure;
    protected bool $includeBom;

    public function __construct(array $config = [])
    {
        $config = array_merge(config('atik-pdf.csv_engine', []), $config);
        $this->delimiter = $config['delimiter'] ?? ',';
        $this->enclosure = $config['enclosure'] ?? '"';
        $this->includeBom = $config['include_bom'] ?? false;
    }

    public function fromTable(array|\Iterator $rows, array $columns = [], string $title = ''): self
    {
        $output = fopen('php://temp', 'r+');

        if ($this->includeBom) {
            fwrite($output, "\xEF\xBB\xBF");
        }

        if ($title) {
            fputcsv($output, [$title], $this->delimiter, $this->enclosure);
        }

        if (!empty($columns)) {
            fputcsv($output, $columns, $this->delimiter, $this->enclosure);
        }

        foreach ($rows as $row) {
            fputcsv($output, $row, $this->delimiter, $this->enclosure);
        }

        rewind($output);
        $this->content = stream_get_contents($output);
        fclose($output);

        return $this;
    }

    public function stream(string $filename = 'document.csv')
    {
        return response($this->output())
            ->header('Content-Type', $this->contentType)
            ->header('Content-Disposition', 'inline; filename="' . $filename . '"');
    }

    public function download(string $filename = 'document.csv')
    {
        return response($this->output())
            ->header('Content-Type', $this->contentType)
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function save(string $path, string $disk = null): bool
    {
        $disk = $disk ?? config('atik-pdf.queue.disk', 'local');
        return Storage::disk($disk)->put($path, $this->output());
    }

    public function output(): string
    {
        return $this->content;
    }

    public function getContentType(): string
    {
        return $this->contentType;
    }

    public function getExtension(): string
    {
        return $this->extension;
    }
}
