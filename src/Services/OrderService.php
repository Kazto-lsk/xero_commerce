<?php

namespace Xpressengine\Plugins\XeroCommerce\Services;

use Xpressengine\Http\Request;
use Xpressengine\Plugins\XeroCommerce\Handlers\OrderHandler;
use Xpressengine\Plugins\XeroCommerce\Handlers\CartHandler;
use Xpressengine\Plugins\XeroCommerce\Models\Order;
use Xpressengine\Plugins\XeroCommerce\Models\OrderItem;

class OrderService
{
    /**
     * @var OrderHandler
     */
    protected $orderHandler;

    public function __construct()
    {
        $this->orderHandler = app('xero_commerce.orderHandler');
    }

    public function order(Request $request)
    {
        return $this->orderHandler->register($this->getCartsFromRequest($request));
    }

    private function getCartsFromRequest(Request $request)
    {
        $cartService = new CartService();
        return $cartService->getCartsById($request->get('cart_id'));
    }

    public function summary($order = null)
    {
        if (!is_null($order)) return $this->orderHandler->getSummary($order->orderItems);
        return $this->orderHandler->getSummary($order = null);
    }

    public function pay(Order $order, Request $request)
    {
        return $this->orderHandler->makePayment($order);
    }

    public function complete(Order $order, Request $request)
    {
        return $this->orderHandler->makeDelivery($order, $request);
    }

    public function orderItemList(Order $order)
    {
        return $this->orderHandler->getOrderItemList($order);
    }

    public function orderList($page, $count, $query)
    {
        return $this->orderHandler->getOrderList($page, $count, $query)->map(function(Order $order) {

            return $this->orderDetail($order);
        });
    }

    public function orderDetail(Order $order)
    {
        $order->orderItems = $this->orderHandler->getOrderItemList($order);
        $order->status = $order->getStatus();
        $order->load('payment', 'userInfo');
        return $order;
    }

    public function dashBoard()
    {
        return $this->orderHandler->dashboard();
    }

    PUBLIC function setShipNo(Request $request)
    {
        foreach($request->order_items as $items) {
            $this->orderHandler->shipNoRegister(OrderItem::find($items['id']), $items['no']);
        }
    }
}
