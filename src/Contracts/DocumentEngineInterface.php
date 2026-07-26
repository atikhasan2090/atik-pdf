<?php

namespace Atik\\PdfExcel\\Contracts;

interface DocumentEngineInterface
{
    public function fromTable(array|\Iterator $rows, array $columns = [], string $title = ''): self;

    public function stream(string $filename = 'document');

    public function download(string $filename = 'document');

    public function save(string $path, string $disk = null): bool;

    public function output(): string;

    public function getContentType(): string;

    public function getExtension(): string;
}
