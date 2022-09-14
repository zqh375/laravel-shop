<?php


namespace App\Nova\Actions;

use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\LaravelNovaExcel\Actions\DownloadExcel;

class ExportOrderAction extends DownloadExcel implements WithMapping

{
    public function map($order): array
    {
        return [
           $order->no,
            $order->total_amount
        ];
    }

    public function name()
    {
        return '导出';
    }

}
