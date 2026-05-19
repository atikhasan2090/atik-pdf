<?php

namespace Atik\Pdf\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Atik\Pdf\Engines\PythonEngine;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class GenerateLargePdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600; // 10 minutes max for huge PDFs

    protected array $data;
    protected ?string $webhookUrl;
    protected string $outputPath;
    protected string $disk;

    /**
     * Create a new job instance.
     */
    public function __construct(array $data, string $outputPath, string $disk = 'local', ?string $webhookUrl = null)
    {
        $this->data = $data;
        $this->outputPath = $outputPath;
        $this->disk = $disk;
        $this->webhookUrl = $webhookUrl;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Always use Python engine for large background jobs
        $engine = new PythonEngine();
        $engine->fromTable(
            $this->data['rows'] ?? [],
            $this->data['columns'] ?? [],
            $this->data['title'] ?? ''
        );
        
        // Save the generated PDF
        $engine->save($this->outputPath, $this->disk);

        // Notify webhook if set
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
