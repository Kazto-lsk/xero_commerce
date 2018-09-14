<?php

namespace Xpressengine\Plugins\XeroStore\Controllers;

use App\Http\Controllers\Controller;
use Xpressengine\Plugins\XeroStore\Models\Cart;
use Xpressengine\Plugins\XeroStore\Models\ProductOptionItem;
use Xpressengine\Plugins\XeroStore\Services\CartService;

class CartController extends Controller
{
    public $cartService;

    public function __construct()
    {
        $this->cartService = new CartService();
    }

    public function index()
    {
        return \XePresenter::make(
            'xero_store::views.cart',
            [
                'title' => '장바구니',
                'carts' => $this->cartService->getList()->groupBy('product_id')
            ]);
    }

    public function add(ProductOptionItem $optionItem)
    {
        $this->cartService->addList($optionItem);
        return redirect()->route('xero_store::cart.index');
    }

    public function draw(Cart $cart)
    {
        $this->cartService->drawList($cart);
        return redirect()->route('xero_store::cart.index');
    }
}
