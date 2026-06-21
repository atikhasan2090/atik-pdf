<?php

namespace Atik\Pdf\Contracts;

interface PdfEngineInterface extends DocumentEngineInterface
{
    public function fromView(string $view, array $data = []): self;
}
