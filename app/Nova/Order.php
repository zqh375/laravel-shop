<?php

namespace App\Nova;

use App\Nova\Actions\ExportOrderAction;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\HasOne;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Maatwebsite\LaravelNovaExcel\Actions\DownloadExcel;

class Order extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\Order::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
    ];

    public static function authorizable()
    {
        return true;
    }

    public static function label()
    {
        return "订单管理";
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make(__('ID'), 'id')->sortable(),
            Number::make('订单编号', 'no')->sortable()->readonly(),
            Number::make('订单总额', 'total_amount')->sortable()->readonly(),
            DateTime::make('支付时间', 'paid_at')->sortable()->readonly(),
            Text::make('付款方式', 'payment_method')->sortable()->readonly(),
            Text::make('付款编号', 'payment_no')->sortable()->readonly(),
            Text::make('退款状态', 'refund_status',function (){
                if($this->refund_status==='pending'){
                    return '未退款';
                }elseif ($this->refund_status==='applied'){
                    return '已申请退款';
                }elseif($this->refund_status==='success'){
                    return '退款成功';
                }else{
                    return '已申请退款';
                }
            })->hideFromIndex()->sortable(),
            Text::make('退款编号', 'refund_no')->hideFromIndex()->sortable(),
            Text::make('关闭状态', 'closed')->sortable(),
            Text::make('物流状态', 'ship_status',function (){
                if($this->ship_status==='delivered'){
                    return '已发货';
                }
                return '未发货';
            })->hideFromIndex()->sortable()->readonly(),
            Text::make('物流公司', function () {
                return $this->ship_data['express_company']??'';
            })->hideFromIndex()->sortable()->readonly(),
            Text::make('物流单号', function () {
                return $this->ship_data['express_no']??'';
            })->hideFromIndex()->sortable()->readonly(),
            HasOne::make('用户', 'user', User::class),
            HasMany::make('订单项', 'items', OrderItem::class),

        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [
            (new ExportOrderAction())->cancelButtonText('取消')
                ->confirmButtonText('确定')
                ->confirmText('是否导出订单')
                ->onlyOnIndex()
                ->withHeadings('订单id', '订单总额')
                ->withFilename(date('YmdHis') . '-订单导出.xlsx'),
            new Actions\TrackAction(),
            new Actions\RefundAction()
        ];
    }
}
