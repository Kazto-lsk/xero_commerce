<?php

namespace Xpressengine\Plugins\XeroCommerce\Services;

use Xpressengine\Http\Request;
use Xpressengine\Plugins\XeroCommerce\Handlers\OrderHandler;
use Xpressengine\Plugins\XeroCommerce\Handlers\CartHandler;
use Xpressengine\Plugins\XeroCommerce\Models\Order;

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

    public function complete(Order $order)
    {
        return $this->orderHandler;
    }
}