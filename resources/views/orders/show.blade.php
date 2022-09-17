@extends('layouts.app')
@section('title', '查看订单')

@section('content')
    <div class="row">
        <div class="col-lg-10 offset-lg-1">
            <div class="card">
                <div class="card-header">
                    <h4>订单详情</h4>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>商品信息</th>
                            <th class="text-center">单价</th>
                            <th class="text-center">数量</th>
                            <th class="text-right item-amount">小计</th>
                        </tr>
                        </thead>
                        @foreach($order->items as $index => $item)
                            <tr>
                                <td class="product-info">
                                    <div class="preview">
                                        <a target="_blank" href="{{ route('products.show', [$item->product_id]) }}">
                                            <img src="{{ $item->product->image_url }}">
                                        </a>
                                    </div>
                                    <div>
              <span class="product-title">
                 <a target="_blank" href="{{ route('products.show', [$item->product_id]) }}">{{ $item->product->title }}</a>
              </span>
                                        <span class="sku-title">{{ $item->productSku->title }}</span>
                                    </div>
                                </td>
                                <td class="sku-price text-center vertical-middle">￥{{ $item->price }}</td>
                                <td class="sku-amount text-center vertical-middle">{{ $item->amount }}</td>
                                <td class="item-amount text-right vertical-middle">￥{{ number_format($item->price * $item->amount, 2, '.', '') }}</td>
                            </tr>
                        @endforeach
                        <tr><td colspan="4"></td></tr>
                    </table>
                    <div class="order-bottom">
                        <div class="order-info">
                            <div class="line"><div class="line-label">收货地址：</div><div class="line-value">{{ join(' ', $order->address) }}</div></div>
                            <div class="line"><div class="line-label">订单备注：</div><div class="line-value">{{ $order->remark ?: '-' }}</div></div>
                            <div class="line"><div class="line-label">订单编号：</div><div class="line-value">{{ $order->no }}</div></div>
                            <div class="line"><div class="line-label">订单编号：</div><div class="line-value">{{ $order->no }}</div></div>
                            <!-- 输出物流状态 -->
                            <div class="line">
                                <div class="line-label">物流状态：</div>
                                <div class="line-value">{{ \App\Models\Order::$shipStatusMap[$order->ship_status] }}</div>
                            </div>
                            <!-- 如果有物流信息则展示 -->
                            @if($order->ship_data)
                                <div class="line">
                                    <div class="line-label">物流信息：</div>
                                    <div class="line-value">{{ $order->ship_data['express_company'] }} {{ $order->ship_data['express_no'] }}</div>
                                </div>
                            @endif
                        <!-- 订单已支付，且退款状态不是未退款时展示退款信息 -->
                            @if($order->paid_at && $order->refund_status !== \App\Models\Order::REFUND_STATUS_PENDING)
                                <div class="line">
                                    <div class="line-label">退款状态：</div>
                                    <div class="line-value">{{ \App\Models\Order::$refundStatusMap[$order->refund_status] }}</div>
                                </div>
                                <div class="line">
                                    <div class="line-label">退款理由：</div>
                                    <div class="line-value">{{ $order->extra['refund_reason'] }}</div>
                                </div>
                            @endif
                        </div>
                        <div class="order-summary text-right">
                            <!-- 展示优惠信息开始 -->
                            @if($order->couponCode)
                                <div class="text-primary">
                                    <span>优惠信息：</span>
                                    <div class="value">{{ $order->couponCode->description }}</div>
                                </div>
                            @endif
                        <!-- 展示优惠信息结束 -->
                            <div class="total-amount">
                                <span>订单总价：</span>
                                <div class="value">￥{{ $order->total_amount }}</div>
                            </div>
                            <div class="total-amount">
                                <span>订单总价：</span>
                                <div class="value">￥{{ $order->total_amount }}</div>
                            </div>
                            <div>
                                <span>订单状态：</span>
                                <div class="value">
                                    @if($order->paid_at)
                                        @if($order->refund_status === \App\Models\Order::REFUND_STATUS_PENDING)
                                            已支付
                                        @else
                                            {{ \App\Models\Order::$refundStatusMap[$order->refund_status] }}
                                        @endif
                                    @elseif($order->closed)
                                        已关闭
                                    @else
                                        未支付
                                    @endif
                                </div>
                            </div>
                            @if(isset($order->extra['refund_disagree_reason']))
                                <div>
                                    <span>拒绝退款理由：</span>
                                    <div class="value">{{ $order->extra['refund_disagree_reason'] }}</div>
                                </div>
                            @endif
                        </div>
                        <!-- 支付按钮开始 -->
                        @if(!$order->paid_at && !$order->closed)
                            <div class="payment-buttons">
                                <a class="btn btn-primary btn-sm" href="{{ route('payment.alipay', ['order' => $order->id]) }}">支付宝支付</a>
                            </div>
                            <div id="paypal-button-container">
                            </div>

                            <!-- 分期支付按钮开始 -->
                            <!-- 仅当订单总金额大等于分期最低金额时才展示分期按钮 -->
                            @if ($order->total_amount >= config('app.min_installment_amount'))
                                <button class="btn btn-sm btn-danger" id='btn-installment'>分期付款</button>
                            @endif
                    @endif
                    <!-- 支付按钮结束 -->




                        <!-- 如果订单的发货状态为已发货则展示确认收货按钮 -->
                        @if($order->ship_status === \App\Models\Order::SHIP_STATUS_DELIVERED)
                            <div class="receive-button">

                                <!-- 将原本的表单替换成下面这个按钮 -->
                                <button type="button" id="btn-receive" class="btn btn-sm btn-success">确认收货</button>
                            </div>
                        @endif
                    <!-- 订单已支付，且退款状态是未退款时展示申请退款按钮 -->
                        @if($order->paid_at && $order->refund_status === \App\Models\Order::REFUND_STATUS_PENDING &&
            $order->paid_at &&
            $order->refund_status === \App\Models\Order::REFUND_STATUS_PENDING)
                            <div class="refund-button">
                                <button class="btn btn-sm btn-danger" id="btn-apply-refund">申请退款</button>
                            </div>
                        @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <!-- 分期弹框开始 -->
    <div class="modal fade" id="installment-modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">选择分期期数</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered table-striped text-center">
                        <thead>
                        <tr>
                            <th class="text-center">期数</th>
                            <th class="text-center">费率</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach(config('app.installment_fee_rate') as $count => $rate)
                            <tr>
                                <td>{{ $count }}期</td>
                                <td>{{ $rate }}%</td>
                                <td>
                                    <button class="btn btn-sm btn-primary btn-select-installment" data-count="{{ $count }}">选择</button>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                </div>
            </div>
        </div>
    </div>
    <!-- 分期弹框结束 -->
@endsection
@section('scriptsAfterJs')
    <script src="https://www.paypal.com/sdk/js?client-id={{env('PAYPAL_SANDBOX_CLIENT_ID')}}&currency=USD&intent=capture"></script>
    <script>
        const fundingSources = [
            paypal.FUNDING.PAYPAL
        ]

        for (const fundingSource of fundingSources) {
            const paypalButtonsComponent = paypal.Buttons({
                fundingSource: fundingSource,

                // optional styling for buttons
                // https://developer.paypal.com/docs/checkout/standard/customize/buttons-style-guide/
                style: {
                    shape: 'rect',
                    height: 40,
                },

                // set up the transaction
                createOrder: (data, actions) => {
                    // pass in any options from the v2 orders create call:
                    return fetch("/create_order/{{$order->id}}/paypal", {
                        method: "post",
                    })
                        .then((response) => response.json())
                        .then((order) => order.id);
                },

                // finalize the transaction
                onApprove: (data, actions) => {
                    const captureOrderHandler = (details) => {
                        const payerName = details.payer.name.given_name
                        console.log('Transaction completed!')
                        console.log(details)
                        window.location.href = "/thankyou?id=" + details.id;
                    }

                    return actions.order.capture().then(captureOrderHandler)
                },

                // handle unrecoverable errors
                onError: (err) => {
                    console.error(
                        'An error prevented the buyer from checking out with PayPal',
                    )
                },
            })

            if (paypalButtonsComponent.isEligible()) {
                paypalButtonsComponent
                    .render('#paypal-button-container')
                    .catch((err) => {
                        console.error('PayPal Buttons failed to render')
                    })
            } else {
                console.log('The funding source is ineligible')
            }
        }
        $(document).ready(function() {
            // 确认收货按钮点击事件
            $('#btn-receive').click(function() {
                // 弹出确认框
                swal({
                    title: "确认已经收到商品？",
                    icon: "warning",
                    dangerMode: true,
                    buttons: ['取消', '确认收到'],
                })
                    .then(function(ret) {
                        // 如果点击取消按钮则不做任何操作
                        if (!ret) {
                            return;
                        }
                        // ajax 提交确认操作
                        axios.post('{{ route('orders.received', [$order->id]) }}')
                            .then(function () {
                                // 刷新页面
                                location.reload();
                            })
                    });
            });

            // 退款按钮点击事件
            $('#btn-apply-refund').click(function () {
                swal({
                    text: '请输入退款理由',
                    content: "input",
                }).then(function (input) {
                    // 当用户点击 swal 弹出框上的按钮时触发这个函数
                    if(!input) {
                        swal('退款理由不可空', '', 'error');
                        return;
                    }
                    // 请求退款接口
                    axios.post('{{ route('orders.apply_refund', [$order->id]) }}', {reason: input})
                        .then(function () {
                            swal('申请退款成功', '', 'success').then(function () {
                                // 用户点击弹框上按钮时重新加载页面
                                location.reload();
                            });
                        });
                });
            });
            $('#btn-installment').click(function () {
                // 展示分期弹框
                $('#installment-modal').modal();
            });

            // 选择分期期数按钮点击事件
            $('.btn-select-installment').click(function () {
                // 调用创建分期付款接口
                axios.post('{{ route('payment.installment', ['order' => $order->id]) }}', { count: $(this).data('count') })
                    .then(function (response) {
                        location.href = '/installments/' + response.data.id;
                    })
            });


        });
    </script>
@endsection
