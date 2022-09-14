<?php

namespace App\Http\Controllers;

use App\Events\OrderPaid;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Exceptions\InvalidRequestException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PaymentController extends Controller
{
    //
    public function payByAlipay(Order $order, Request $request)
    {
        // 判断订单是否属于当前用户
        // $this->authorize('own', $order);
        // 订单已支付或者已关闭
        if ($order->paid_at || $order->closed) {
            throw new InvalidRequestException('订单状态不正确');
        }

        // 调用支付宝的网页支付
        return app('alipay')->web([
            'out_trade_no' => $order->no, // 订单编号，需保证在商户端不重复
            'total_amount' => $order->total_amount, // 订单金额，单位元，支持小数点后两位
            'subject' => '支付 Laravel Shop 的订单：' . $order->no, // 订单标题
        ]);
    }


    public function thankyou(Request $request)
    {
        return view('pages.success', ['msg' => '付款成功']);
    }

    // 前端回调页面
    public function alipayReturn()
    {
        try {
            app('alipay')->verify();
        } catch (\Exception $e) {
            return view('pages.error', ['msg' => '数据不正确']);
        }

        return view('pages.success', ['msg' => '付款成功']);
    }

    public function paypalReturn(Request $request)
    {
        $provider = new PayPalClient;
        $provider->getAccessToken();
        $detail = $provider->showOrderDetails($request->input('id'));
        if (!(isset($detail['status']) && $detail['status'] === 'COMPLETED')) {
            return view('pages.error', ['msg' => '数据不正确']);
        }
        return view('pages.success', ['msg' => '付款成功']);
    }

    // 服务器端回调
    public function alipayNotify()
    {
        // 校验输入参数
        $data = app('alipay')->verify();
        // 如果订单状态不是成功或者结束，则不走后续的逻辑
        // 所有交易状态：https://docs.open.alipay.com/59/103672
        if (!in_array($data->trade_status, ['TRADE_SUCCESS', 'TRADE_FINISHED'])) {
            return app('alipay')->success();
        }
        // $data->out_trade_no 拿到订单流水号，并在数据库中查询
        $order = Order::where('no', $data->out_trade_no)->first();
        // 正常来说不太可能出现支付了一笔不存在的订单，这个判断只是加强系统健壮性。
        if (!$order) {
            return 'fail';
        }
        // 如果这笔订单的状态已经是已支付
        if ($order->paid_at) {
            // 返回数据给支付宝
            return app('alipay')->success();
        }

        $order->update([
            'paid_at' => Carbon::now(), // 支付时间
            'payment_method' => 'alipay', // 支付方式
            'payment_no' => $data->trade_no, // 支付宝订单号
        ]);

        Log::info('ALIPAY DEBUG:', $data->all());
        return app('alipay')->success();
    }

    public function webhook(Request $request)
    {
        $provider = new PayPalClient;
        $provider->getAccessToken();
        $data = [
            'transmission_id' => $request->header('PAYPAL-TRANSMISSION-ID'),
            'transmission_time' => $request->header('PAYPAL-TRANSMISSION-TIME'),
            'cert_url' => $request->header('PAYPAL-CERT-URL'),
            'auth_algo' => $request->header('PAYPAL-AUTH-ALGO'),
            'transmission_sig' => $request->header('PAYPAL-TRANSMISSION-SIG'),
            'webhook_id' => env('PAYPAL_WEBHOOK_ID'), // 填你自己应用中的Webhook id
            'webhook_event' => $request->post(),
        ];

        //验证服务端回调是否正确
        $result = $provider->verifyWebHook($data);


        $response = json_decode($request->getContent(), true);
        $invoiceId = $response['resource']['invoice_id'];
        Log::info('invoice_id',['invoice_id'=>$invoiceId]);
        $tradeNo = $response['resource']['id'];
        $orderId = Str::after($invoiceId, 'my-order-');
        //付款成功修改信息
        $order = Order::find($orderId);

        $order->update([
            'paid_at' => Carbon::now(), // 支付时间
            'payment_method' => 'paypal', // 支付方式
            'payment_no' => $tradeNo, // 贝宝订单号
        ]);

        $this->afterPaid($order);

        return $result;
    }


    protected function afterPaid(Order $order)
    {
        event(new OrderPaid($order));
    }
}
