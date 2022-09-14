<?php

namespace App\Nova\Actions;

use App\Exceptions\InvalidRequestException;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Text;

class TrackAction extends Action
{
    use InteractsWithQueue, Queueable;
    public $name = '发货';
    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        //

        foreach ($models as $model){
            if (!$model->paid_at) {
                throw new InvalidRequestException('该订单未付款');
            }
            // 判断当前订单发货状态是否为未发货
            if ($model->ship_status !== Order::SHIP_STATUS_PENDING) {
               // throw new InvalidRequestException('该订单已发货');
            }
            $data['express_company']=$fields->express_company;
            $data['express_no']=$fields->express_no;
            $model->update([
                'ship_status' => Order::SHIP_STATUS_DELIVERED,
                // 我们在 Order 模型的 $casts 属性里指明了 ship_data 是一个数组
                // 因此这里可以直接把数组传过去
                'ship_data'   => $data,
            ]);

        }
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [
            Text::make('物流公司','express_company'),
            Text::make('物流单号','express_no')
        ];
    }
}
