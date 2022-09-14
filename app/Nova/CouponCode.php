<?php

namespace App\Nova;

use App\Nova\Actions\ExportOrderCodeAction;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class CouponCode extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\CouponCode::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    public static function label()
    {
        return "优惠券管理";
    }

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make(__('ID'), 'id')->sortable(),
            Text::make('名称', 'name')->sortable()->rules('required','max:255'),
            Text::make('优惠码', 'code')->sortable()->creationRules('required','unique:coupon_codes')->updateRules('required','unique:coupon_codes,code,{{resourceId}}'),
            Select::make('类型', 'type')->options(\App\Models\CouponCode::$typeMap)->sortable(),
            Number::make('折扣', 'value')->rules(function ()use($request) {
                if ($request->input('type') === \App\Models\CouponCode::TYPE_PERCENT) {
                    // 如果选择了百分比折扣类型，那么折扣范围只能是 1 ~ 99
                    return [ 'required','numeric','between:1,99'];
                } else {
                    // 否则只要大等于 0.01 即可
                    return ['required','numeric','min:0.01'];
                }
            })->sortable(),
            Number::make('总量', 'total')->sortable()->min(0),
            Number::make('最低金额', 'min_amount')->sortable()->min(0),
            DateTime::make('开始时间', 'not_before')->sortable(),
            DateTime::make('结束时间', 'not_after')->sortable(),
            Select::make('启动','enabled')->options(['1' => '是', '0' => '否']),
            DateTime::make('创建时间', 'created_at')->sortable(),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [
            (new ExportOrderCodeAction())->cancelButtonText('取消')
                ->confirmButtonText('确定')
                ->confirmText('是否导出优惠券')
                ->onlyOnIndex()
                ->withHeadings('优惠券id', '优惠券名称','优惠码','类型','折扣','总量','最低金额','开始时间','结束时间','是否启动')
                ->withFilename(date('YmdHis') . '-优惠券导出.xlsx')
            ];
    }
}
