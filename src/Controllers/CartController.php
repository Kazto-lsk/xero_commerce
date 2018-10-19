<?php

namespace Xpressengine\Plugins\XeroCommerce\Controllers;

use App\Http\Controllers\Controller;
use Xpressengine\Http\Request;
use Xpressengine\Plugins\XeroCommerce\Models\Cart;
use Xpressengine\Plugins\XeroCommerce\Models\ProductOptionItem;
use Xpressengine\Plugins\XeroCommerce\Models\SellType;
use Xpressengine\Plugins\XeroCommerce\Models\SellUnit;
use Xpressengine\Plugins\XeroCommerce\Services\CartService;

class CartController extends XeroCommerceBasicController
{
    public $cartService;

    public function __construct()
    {
        parent::__construct();

        $this->cartService = new CartService();
    }

    public function index()
    {
        return \XePresenter::make(
            'cart.index',
            [
                'cartList' => $this->cartService->getJsonList()
            ]
        );
    }

    public function list()
    {
        return $this->cartService->getJsonList();
    }

    public function summary(Request $request)
    {
        return $this->cartService->summary($request);
    }

    public function change(Cart $cart, Request $request)
    {
        return $this->cartService->change($cart, $request);
    }

    public function draw(Cart $cart)
    {
        $this->cartService->draw($cart);
    }

    public function drawList(Request $request)
    {
        $this->cartService->drawList($request->get('cart_id'));
    }
}
