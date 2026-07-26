<?php

namespace Atik\Pdf\Engines;

use Atik\Pdf\Contracts\DocumentEngineInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Facades\Storage;

class ExcelEngine implements DocumentEngineInterface
{
    protected Spreadsheet $spreadsheet;
    protected string $contentType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
    protected string $extension = 'xlsx';

    public function __construct(array $config = [])
    {
        $this->spreadsheet = new Spreadsheet();
        $sheet = $this->spreadsheet->getActiveSheet();

        $config = array_merge(config('laravel-pdf-excel.excel_engine', []), $config);

        if (!empty($config['author'])) {
            $this->spreadsheet->getProperties()->setCreator($config['author']);
        }
    }

    public function fromTable(array|\Iterator $rows, array $columns = [], string $title = ''): self
    {
        $sheet = $this->spreadsheet->getActiveSheet();
        $rowNumber = 1;

        if ($title) {
            $sheet->setCellValue('A' . $rowNumber, $title);
            $sheet->getStyle('A' . $rowNumber)->getFont()->setBold(true)->setSize(14);
            $sheet->mergeCells('A' . $rowNumber . ':' . $sheet->getHighestColumn() . $rowNumber);
            $sheet->getStyle('A' . $rowNumber)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $rowNumber++;
        }

        if (!empty($columns)) {
            $colLetter = 'A';
            foreach ($columns as $col) {
                $cell = $colLetter . $rowNumber;
                $sheet->setCellValue($cell, $col);
                $sheet->getStyle($cell)->getFont()->setBold(true);
                $sheet->getStyle($cell)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('F2F2F2');
                $sheet->getStyle($cell)->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);
                $colLetter++;
            }
            $rowNumber++;
        }

        foreach ($rows as $row) {
            $colLetter = 'A';
            foreach ($row as $cell) {
                $sheet->setCellValue($colLetter . $rowNumber, (string) $cell);
                $sheet->getStyle($colLetter . $rowNumber)->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);
                $colLetter++;
            }
            $rowNumber++;
        }

        foreach (range('A', $sheet->getHighestColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return $this;
    }

    public function stream(string $filename = 'document.xlsx')
    {
        return response($this->output())
            ->header('Content-Type', $this->contentType)
            ->header('Content-Disposition', 'inline; filename="' . $filename . '"');
    }

    public function download(string $filename = 'document.xlsx')
    {
        return response($this->output())
            ->header('Content-Type', $this->contentType)
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function save(string $path, string $disk = null): bool
    {
        $disk = $disk ?? config('laravel-pdf-excel.queue.disk', 'local');
        return Storage::disk($disk)->put($path, $this->output());
    }

    public function output(): string
    {
        $writer = new Xlsx($this->spreadsheet);
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();
        $this->spreadsheet->disconnectWorksheets();
        return $content ?: '';
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
