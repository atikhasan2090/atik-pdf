<?php

namespace Atik\Pdf\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Atik\Pdf\Engines\PythonEngine;
use Atik\Pdf\Engines\ExcelEngine;
use Atik\Pdf\Engines\CsvEngine;
use Atik\Pdf\Contracts\DocumentEngineInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class GenerateLargePdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600;

    protected array $data;
    protected ?string $webhookUrl;
    protected string $outputPath;
    protected string $disk;

    public function __construct(array $data, string $outputPath, string $disk = 'local', ?string $webhookUrl = null)
    {
        $this->data = $data;
        $this->outputPath = $outputPath;
        $this->disk = $disk;
        $this->webhookUrl = $webhookUrl;
    }

    public function handle(): void
    {
        $format = $this->data['format'] ?? 'pdf';

        $engine = match ($format) {
            'excel' => new ExcelEngine(),
            'csv' => new CsvEngine(),
            default => new PythonEngine(),
        };

        $engine->fromTable(
            $this->data['rows'] ?? [],
            $this->data['columns'] ?? [],
            $this->data['title'] ?? ''
        );

        $engine->save($this->outputPath, $this->disk);

        if ($this->webhookUrl) {
            $url = Storage::disk($this->disk)->url($this->outputPath);

            Http::post($this->webhookUrl, [
                'status' => 'completed',
                'file_path' => $this->outputPath,
                'file_url' => $url,
            ]);
        }
    }
}
