<?php

namespace App\Nova;

use Hubertnnn\LaravelNova\Fields\DynamicSelect\DynamicSelect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Image;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Trix;
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
    public static $group = '商品管理';

    public static function label()
    {
        return '商品管理';
    }

    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query->where('type', \App\Models\Product::TYPE_NORMAL);
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
            ID::make()->sortable(),

            Text::make('商品名称', 'title')
                ->sortable()
                ->rules('required', 'max:255'),

            Text::make('商品长标题', 'long_title')
                ->sortable()
                ->rules('required'),
            Number::make('价格', 'price')->hideFromIndex(),

            Image::make('封面图片', 'image')
                ->thumbnail(function ($value, $disk) {
                    return $value
                        ? Str::startsWith($value, 'http') || Str::startsWith($value, 'https')
                            ? $value
                            : Storage::disk('public')->url($value)
                        : null;
                }),

            Trix::make('商品描述', 'description')
                ->rules('required')
                ->alwaysShow(),

            DynamicSelect::make('类目', 'category_id')->options(function () {
                return \App\Models\Category::where('is_directory',0)->pluck('name', 'id')->toArray();
            }),
            Select::make('已上架', 'on_sale')->options([
                '1' => '是',
                '0' => '否',
            ])->displayUsingLabels()->rules('required'),

            HasMany::make('商品SKU', 'skus', ProductSku::class)->hideFromIndex()->inline(),

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
