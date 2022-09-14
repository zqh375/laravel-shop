<?php

namespace App\Nova;

use Hubertnnn\LaravelNova\Fields\DynamicSelect\DynamicSelect;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Image;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class Product extends Resource
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
    public static $title = 'title';


    public static function label()
    {
        return '商品管理';
    }


    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'title'
    ];

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
            Text::make('标题', 'title')->sortable()
                ->rules('required', 'max:254'),
            Text::make(__('描述'), 'description')->hideFromIndex()->sortable(),
            Image::make(__('图片'), 'image')->hideFromIndex()->sortable(),
            Text::make(__('是否在售'), 'on_sale')->sortable(),
            Text::make(__('评论星数'), 'rating')->hideFromIndex()->sortable(),
            Text::make(__('库粗数量'), 'sold_count')->sortable(),
            Text::make(__('评论数量'), 'review_count')->hideFromIndex()->sortable(),
            Text::make(__('价格'), 'price')->sortable(),
            HasMany::make('sku商品', 'skus', ProductSku::class)->hideFromIndex()->inline(),
            DynamicSelect::make('类目', 'category_id')->options(function () {
                return \App\Models\Category::where('is_directory',0)->pluck('name', 'id')->toArray();
            }),
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
        return [];
    }
}
