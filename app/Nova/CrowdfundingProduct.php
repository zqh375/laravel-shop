<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\HasOne;
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

    public static $group = '商品管理';
    public static function label()
    {
        return '众筹商品';
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
            ID::make(__('ID'), 'id')->sortable(),
            Text::make('商品名称', 'title')->rules('required'),
            BelongsTo::make('类目', 'category', Category::class)->searchable()->hideFromIndex(),
            Image::make('图片','image')->disk('local')->preview(function ($url) {
                // 如果 image 字段本身就已经是完整的 url 就直接返回
                if (Str::startsWith($url, ['http://', 'https://'])) {
                    return $url;
                }
                return Storage::disk('local')->url($url);
            })->creationRules('required')
                ->hideFromIndex(),
            \Laravel\Nova\Fields\Trix::make('描述','description')->rules('required'),
            Boolean::make('已上架','on_sale'),
            HasOne::make('众筹信息','crowdfunding', CrowdfundingProduct::class)->inline()->requireChild(),
            HasMany::make('商品sku','skus', ProductSku::class)->inline()->requireChild(),
            Number::make('价格', 'price')->min(0),
            Number::make('目前金额','crowdfunding.total_amount')->hideWhenUpdating()->hideWhenCreating(),
            Select::make('类型','type')->options(function (){
                return [
                    \App\Models\Product::TYPE_CROWDFUNDING => '众筹商品',
                ];
            })->default(function () {
                return \App\Models\Product::TYPE_CROWDFUNDING;
            })->hideFromIndex()->hideFromDetail(),
            Select::make('状态', function ($product) {
                return \App\Models\CrowdfundingProduct::$statusMap[ $product->crowdfunding->status ?? ''] ?? '';
            }),
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

    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query->where('type', \App\Models\Product::TYPE_CROWDFUNDING);
    }

    public static function relatableQuery(NovaRequest $request, $query)
    {

        $requestSegment = strtolower($request->segment(4));

        if ($requestSegment === 'category') {
            return $query->where('is_directory', false);
        }
        return $query;
    }
}
