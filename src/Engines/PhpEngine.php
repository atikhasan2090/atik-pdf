<?php

namespace Atik\Pdf\Engines;

use Atik\Pdf\Contracts\PdfEngineInterface;
use Mpdf\Mpdf;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;

class PhpEngine implements PdfEngineInterface
{
    protected Mpdf $mpdf;
    protected string $html = '';

    public function __construct(array $config = [])
    {
        $defaultConfig = config('atik-pdf.php_engine', []);
        $mergedConfig = array_merge($defaultConfig, $config);

        // Map laravel config to mPDF expected keys if needed
        $mpdfConfig = [
            'mode' => $mergedConfig['mode'] ?? 'utf-8',
            'format' => $mergedConfig['format'] ?? 'A4',
            'default_font' => $mergedConfig['default_font'] ?? 'nikosh',
            'margin_left' => $mergedConfig['margin_left'] ?? 15,
            'margin_right' => $mergedConfig['margin_right'] ?? 15,
            'margin_top' => $mergedConfig['margin_top'] ?? 16,
            'margin_bottom' => $mergedConfig['margin_bottom'] ?? 16,
            'margin_header' => $mergedConfig['margin_header'] ?? 9,
            'margin_footer' => $mergedConfig['margin_footer'] ?? 9,
            'tempDir' => $mergedConfig['temp_dir'] ?? storage_path('app/temp/mpdf'),
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
        ];

        // Ensure temp directory exists
        if (!is_dir($mpdfConfig['tempDir'])) {
            mkdir($mpdfConfig['tempDir'], 0777, true);
        }

        $this->mpdf = new Mpdf($mpdfConfig);
    }

    public function fromView(string $view, array $data = []): self
    {
        $this->html = View::make($view, $data)->render();
        $this->mpdf->WriteHTML($this->html);
        return $this;
    }

    public function fromTable(array|\Iterator $rows, array $columns = [], string $title = ''): self
    {
        // Simple HTML table generation for mPDF
        $html = '<h2 style="text-align:center;">' . htmlspecialchars($title) . '</h2>';
        $html .= '<table border="1" cellpadding="5" cellspacing="0" style="width: 100%; border-collapse: collapse; font-family: nikosh, sans-serif;">';
        
        if (!empty($columns)) {
            $html .= '<thead><tr>';
            foreach ($columns as $col) {
                $html .= '<th style="background-color: #f2f2f2;">' . htmlspecialchars($col) . '</th>';
            }
            $html .= '</tr></thead>';
        }

        $html .= '<tbody>';
        foreach ($rows as $row) {
            $html .= '<tr>';
            foreach ($row as $cell) {
                $html .= '<td>' . htmlspecialchars((string) $cell) . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';

        $this->html = $html;
        $this->mpdf->WriteHTML($this->html);
        
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
        $disk = $disk ?? config('atik-pdf.queue.disk', 'local');
        return Storage::disk($disk)->put($path, $this->output());
    }

    public function output(): string
    {
        return $this->mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN);
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
