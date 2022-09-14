<?php


namespace App\Nova\Actions;


use App\Models\CouponCode;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\LaravelNovaExcel\Actions\DownloadExcel;

class ExportOrderCodeAction extends DownloadExcel implements WithMapping
{
    public function map($couponCode): array
    {
        return [
            $couponCode->id,
            $couponCode->name,
            $couponCode->code,
            CouponCode::$typeMap[$couponCode->type]??'',
            $couponCode->value,
            $couponCode->total,
            $couponCode->min_amount,
            $couponCode->not_before,
            $couponCode->not_after,
            $couponCode->enabled?'是':'否'
        ];
    }

    public function name()
    {
        return '导出';
    }
}
