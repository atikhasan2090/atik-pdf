<?php

namespace Atik\\PdfExcel\\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Atik\\PdfExcel\\PdfExcelManager auto(array|null $data = null)
 * @method static \Atik\\PdfExcel\\PdfExcelManager view(string $view, array $data = [])
 * @method static \Atik\\PdfExcel\\PdfExcelManager table(array $rows, array $columns = [], string $title = '')
 * @method static \Atik\\PdfExcel\\PdfExcelManager largeTable(array|\Iterator $rows, array $columns = [], string $title = '')
 * @method static \Atik\\PdfExcel\\PdfExcelManager excel()
 * @method static \Atik\\PdfExcel\\PdfExcelManager csv()
 * @method static \Atik\\PdfExcel\\PdfExcelManager async(array $rows, array $columns = [], string $title = '')
 * @method static \Atik\\PdfExcel\\PdfExcelManager webhook(string $url)
 * @method static \Atik\\PdfExcel\\PdfExcelManager watermark(string $textOrImagePath)
 * @method static \Illuminate\Http\Response stream(string $filename = 'document')
 * @method static \Illuminate\Http\Response download(string $filename = 'document')
 * @method static bool save(string $path, string $disk = null)
 * @method static void queue()
 *
 * @see \Atik\\PdfExcel\\PdfExcelManager
 */
class PdfExcel extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'laravel-pdf-excel';
    }
}
