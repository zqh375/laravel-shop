<?php

namespace App\Nova;

use Hubertnnn\LaravelNova\Fields\DynamicSelect\DynamicSelect;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Avatar;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class Category extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\Category::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    public static function label()
    {
        return "分类管理";
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
            Text::make('名称','name'),
            Text::make('层级','level')->hideWhenCreating()->hideWhenUpdating(),
            Select::make('是否目录','is_directory')
                ->options(['1' => '是', '0' => '否'])->readonly(function (){

                }),
            Text::make('路径','path')->hideWhenCreating()->hideWhenUpdating(),
            DynamicSelect::make('父类目', 'parent_id')->options(function () {
                if($this->id){
                    return \App\Models\Category::where('id', '!=',$this->id)
                        ->pluck('name', 'id')
                        ->toArray();
                }else{
                    return \App\Models\Category::pluck('name', 'id')->toArray();
                }
            })->hideFromIndex()->rules('required')->hideWhenCreating(function(){
                if( ! \App\Models\Category::exists()){
                    return true;
                }else{
                    return false;
                }
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
}
