<?php

namespace Xpressengine\Plugins\XeroCommerce\Services;

use Xpressengine\Http\Request;
use Xpressengine\Plugins\XeroCommerce\Handlers\ProductHandler;

class ProductService
{
    /** @var ProductHandler $handler */
    protected $handler;

    /**
     * ProductService constructor.
     */
    public function __construct()
    {
        $this->handler = app('xero_commerce.productHandler');
    }

    public function getProducts(Request $request)
    {
        $query = $this->handler->getProductsQuery($request);

        return $query->get();
    }

    public function getProduct($productId)
    {
        $product = $this->handler->getProduct($productId);

        return $product;
    }
}
