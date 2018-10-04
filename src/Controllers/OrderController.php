<?php

namespace Xpressengine\Plugins\XeroCommerce\Controllers;

use App\Facades\XeFrontend;
use App\Http\Controllers\Controller;
use Xpressengine\Http\Request;
use Xpressengine\Plugins\XeroCommerce\Models\Cart;
use Xpressengine\Plugins\XeroCommerce\Models\Order;
use Xpressengine\Plugins\XeroCommerce\Plugin;
use Xpressengine\Plugins\XeroCommerce\Services\AgreementService;
use Xpressengine\Plugins\XeroCommerce\Services\OrderService;
use Xpressengine\Plugins\XeroCommerce\Services\CartService;

class OrderController extends XeroCommerceBasicController
{
    public $orderService;

    public function __construct()
    {
        parent::__construct();

        $this->orderService = new OrderService();
    }

    public function index()
    {
        return \XePresenter::make('order.dash', ['title' => '주문내역', 'dashboard' => $this->orderService->dashBoard()]);
    }

    public function register(Request $request)
    {
        $order = $this->orderService->order($request);
        return [
            'url' => route('xero_commerce::order.register.again'),
            'order_id' => $order->id
        ];
    }

    public function registerAgain(Request $request)
    {
        $order = Order::find($request->order_id);
        return \XePresenter::make(
            'order.register',
            ['title' => 'test',
                'agreements' => [
                    'purchase' => AgreementService::get('purchase'),
                    'privacy' => AgreementService::get('privacy'),
                    'thirdParty' => AgreementService::get('thirdParty')
                ],
                'order' => $order,
                'orderItems' => $this->orderService->orderItemList($order),
                'summary' => $this->orderService->summary($order)]
        );
    }

    public function fail(Order $order)
    {
        return \XePresenter::make(
            'order.fail',
            [
                'title' => 'fail',
                'order' => $order
            ]
        );
    }

    public function success(Request $request, Order $order)
    {
        $this->orderService->pay($order, $request);
        $cartService = new CartService();
        $cartService->drawList(Cart::where('order_id', $order->id)->pluck('id'));
        return $this->orderService->complete($order, $request);
    }

    public function pay(Order $order, Request $request)
    {
        return $this->orderService->pay($order, $request);
    }

    public function afterService()
    {
        return \XePresenter::make('order.as', ['title' => '주문내역']);
    }
}
