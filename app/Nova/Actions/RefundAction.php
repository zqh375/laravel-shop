<?php

namespace App\Nova\Actions;

use App\Exceptions\InternalException;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Log;

class RefundAction extends Action
{
    use InteractsWithQueue, Queueable;


    public $name = '退款';
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
            // 是否同意退款
            if ($fields->agree) {
                // 清空拒绝退款理由
                $extra = $model->extra ?: [];
                unset($extra['refund_disagree_reason']);
                $model->update([
                    'extra' => $extra,
                ]);
                // 调用退款逻辑
                $this->_refundOrder($model);
            } else {
                // 将拒绝退款理由放到订单的 extra 字段中
                $extra = $model->extra ?: [];
                $extra['refund_disagree_reason'] = $fields->reason;
                // 将订单的退款状态改为未退款
                $model->update([
                    'refund_status' => Order::REFUND_STATUS_PENDING,
                    'extra'         => $extra,
                ]);
            }
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
            Select::make('是否同意','agree')->options([1=>'同意',0=>'不同意']),
            Text::make('输入拒绝退款理由','reason')
        ];
    }

    protected function _refundOrder(Order $order)
    {
        // 判断该订单的支付方式
        switch ($order->payment_method) {
            case 'paypal':
                $this->refundOrderByPaypal($order);
            case 'wechat':
                // 微信的先留空
                // todo
                break;
            case 'alipay':
                // 用我们刚刚写的方法来生成一个退款订单号
                $refundNo = Order::getAvailableRefundNo();
                // 调用支付宝支付实例的 refund 方法
                $ret = app('alipay')->refund([
                    'out_trade_no' => $order->no, // 之前的订单流水号
                    'refund_amount' => $order->total_amount, // 退款金额，单位元
                    'out_request_no' => $refundNo, // 退款订单号
                ]);
                // 根据支付宝的文档，如果返回值里有 sub_code 字段说明退款失败
                if ($ret->sub_code) {
                    // 将退款失败的保存存入 extra 字段
                    $extra = $order->extra;
                    $extra['refund_failed_code'] = $ret->sub_code;
                    // 将订单的退款状态标记为退款失败
                    $order->update([
                        'refund_no' => $refundNo,
                        'refund_status' => Order::REFUND_STATUS_FAILED,
                        'extra' => $extra,
                    ]);
                } else {
                    // 将订单的退款状态标记为退款成功并保存退款订单号
                    $order->update([
                        'refund_no' => $refundNo,
                        'refund_status' => Order::REFUND_STATUS_SUCCESS,
                    ]);
                }
                break;
            default:
                // 原则上不可能出现，这个只是为了代码健壮性
                throw new InternalException('未知订单支付方式：'.$order->payment_method);
                break;
        }
    }

    protected function refundOrderByPaypal(Order $order)
    {
        try {
            $provider = new PayPalClient;
            $provider->getAccessToken();
            $refund_amount = $order->total_amount;
            $note = $this->order->extra['refund_reason'] ?? 'refund';
            $response = $provider->refundCapturedPayment($order->payment_no, "my-order-{$order->id}", $refund_amount, $note);
            Log::info('refund_res',[$response]);
            if (isset($response['status'])&&$response['status'] === 'COMPLETED') { //todo 确认退款成功的壮体啊
                $order->update([
                    'refund_status' =>   Order::REFUND_STATUS_SUCCESS,
                    'refund_no'=>$response['id']
                ]);
            } else {
                $order->update([
                    'refund_status' => Order::REFUND_STATUS_FAILED,
                ]);
            }
        } catch (\Exception $exception) {
            Log::error('payment_refund_message:' . $exception->getMessage());
            return false;
        }
    }
}
