<?php

namespace Atik\\PdfExcel\\Contracts;

interface PdfEngineInterface extends DocumentEngineInterface
{
    public function fromView(string $view, array $data = []): self;
}
