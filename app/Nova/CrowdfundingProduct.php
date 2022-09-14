<?php

namespace App\Nova;

use Hubertnnn\LaravelNova\Fields\DynamicSelect\DynamicSelect;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Image;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class CrowdfundingProduct extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\Product::class;

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

    public static function label()
    {
        return "众筹商品列表";
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            Text::make('标题', 'title')->sortable()
                ->rules('required', 'max:254'),
            Text::make(__('描述'), 'description')->hideFromIndex()->sortable(),
            Image::make(__('图片'), 'image')->hideFromIndex()->sortable(),
            Text::make(__('是否在售'), 'on_sale')->sortable(),
            Number::make('目标金额','price'),
            Number::make('众筹目标金额',function (){
                return $this->crowdfunding->target_amount??0;
            }),
            DateTime::make('结束时间',function (){
                return $this->crowdfunding->end_at??'';
            }),
            Text::make('状态','status'),
            Text::make('类型','type')->hideWhenCreating()->hideWhenUpdating()->hideFromDetail(),
            DynamicSelect::make('类目', 'category_id')->options(function () {
                return \App\Models\Category::where('is_directory',0)->pluck('name', 'id')->toArray();
            }),
            HasMany::make('sku商品', 'skus', ProductSku::class)->hideFromIndex()->inline(),


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
        return [];
    }
}
