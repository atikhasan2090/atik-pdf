<?php

namespace Atik\Pdf\Contracts;

interface PdfEngineInterface
{
    /**
     * Generate PDF from HTML view
     */
    public function fromView(string $view, array $data = []): self;

    /**
     * Generate PDF from table data
     */
    public function fromTable(array|\Iterator $rows, array $columns = [], string $title = ''): self;

    /**
     * Stream the PDF to the browser
     */
    public function stream(string $filename = 'document.pdf');

    /**
     * Download the PDF
     */
    public function download(string $filename = 'document.pdf');

    /**
     * Save the PDF to a file path
     */
    public function save(string $path, string $disk = null): bool;
    
    /**
     * Get the raw PDF content
     */
    public function output(): string;
}
