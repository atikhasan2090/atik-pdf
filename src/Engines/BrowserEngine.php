<?php

namespace Atik\\PdfExcel\\Engines;

use Atik\\PdfExcel\\Contracts\PdfEngineInterface;
use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Storage;
use Exception;

class BrowserEngine implements PdfEngineInterface
{
    protected string $html = '';
    protected array $browsershotOptions = [];

    public function __construct(array $config = [])
    {
        $this->browsershotOptions = array_merge(
            config('laravel-pdf-excel.browser_engine', []),
            $config
        );
    }

    public function fromView(string $view, array $data = []): self
    {
        $this->html = View::make($view, $data)->render();
        return $this;
    }

    public function fromTable(array|\Iterator $rows, array $columns = [], string $title = ''): self
    {
        // Convert iterator to array if needed
        $rowsArray = is_array($rows) ? $rows : iterator_to_array($rows);

        // Standard HTML table generation
        $html = '<!DOCTYPE html><html><head><meta charset="utf-8">';
        
        // Link Bootstrap from CDN by default or local if configured
        $html .= '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">';
        $html .= '<style>body { padding: 20px; font-family: system-ui, sans-serif; }</style>';
        $html .= '</head><body>';
        
        if ($title) {
            $html .= '<h2 class="text-center mb-4">' . htmlspecialchars($title) . '</h2>';
        }
        
        $html .= '<table class="table table-bordered table-striped">';
        
        if (!empty($columns)) {
            $html .= '<thead class="table-dark"><tr>';
            foreach ($columns as $col) {
                $html .= '<th>' . htmlspecialchars($col) . '</th>';
            }
            $html .= '</tr></thead>';
        }

        $html .= '<tbody>';
        foreach ($rowsArray as $row) {
            $html .= '<tr>';
            foreach ($row as $cell) {
                $html .= '<td>' . htmlspecialchars((string) $cell) . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';
        $html .= '</body></html>';

        $this->html = $html;
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
        if (empty($this->html)) {
            throw new Exception("No HTML content to generate PDF from. Call fromView() or fromTable() first.");
        }

        $browsershot = Browsershot::html($this->html);

        // Apply configuration options
        if (!empty($this->browsershotOptions['node_binary'])) {
            $browsershot->setNodeBinary($this->browsershotOptions['node_binary']);
        }
        if (!empty($this->browsershotOptions['npm_binary'])) {
            $browsershot->setNpmBinary($this->browsershotOptions['npm_binary']);
        }
        if (!empty($this->browsershotOptions['node_module_path'])) {
            $browsershot->setNodeModulePath($this->browsershotOptions['node_module_path']);
        }
        if (!empty($this->browsershotOptions['chrome_path'])) {
            $browsershot->setChromePath($this->browsershotOptions['chrome_path']);
        }
        if (!empty($this->browsershotOptions['no_sandbox'])) {
            $browsershot->noSandbox();
        }
        if (!empty($this->browsershotOptions['args'])) {
            $browsershot->addChromiumArguments($this->browsershotOptions['args']);
        }

        // Apply default format and margin options
        $format = $this->browsershotOptions['format'] ?? 'A4';
        $browsershot->format($format);

        if (isset($this->browsershotOptions['margins'])) {
            $margins = $this->browsershotOptions['margins'];
            $browsershot->margins(
                $margins['top'] ?? 0,
                $margins['right'] ?? 0,
                $margins['bottom'] ?? 0,
                $margins['left'] ?? 0
            );
        }

        return $browsershot->pdf();
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
