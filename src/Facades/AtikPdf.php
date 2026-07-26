<?php

namespace Atik\Pdf\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Atik\Pdf\AtikPdfManager auto(array|null $data = null)
 * @method static \Atik\Pdf\AtikPdfManager view(string $view, array $data = [])
 * @method static \Atik\Pdf\AtikPdfManager table(array $rows, array $columns = [], string $title = '')
 * @method static \Atik\Pdf\AtikPdfManager largeTable(array|\Iterator $rows, array $columns = [], string $title = '')
 * @method static \Atik\Pdf\AtikPdfManager excel()
 * @method static \Atik\Pdf\AtikPdfManager csv()
 * @method static \Atik\Pdf\AtikPdfManager async(array $rows, array $columns = [], string $title = '')
 * @method static \Atik\Pdf\AtikPdfManager webhook(string $url)
 * @method static \Atik\Pdf\AtikPdfManager watermark(string $textOrImagePath)
 * @method static \Illuminate\Http\Response stream(string $filename = 'document')
 * @method static \Illuminate\Http\Response download(string $filename = 'document')
 * @method static bool save(string $path, string $disk = null)
 * @method static void queue()
 *
 * @see \Atik\Pdf\AtikPdfManager
 */
class AtikPdf extends Facade
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
