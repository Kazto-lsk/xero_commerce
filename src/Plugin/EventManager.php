<?php

namespace Xpressengine\Plugins\XeroCommerce\Plugin;

use Illuminate\Support\Facades\Notification;
use Xpressengine\Plugins\XeroCommerce\Events\NewProductRegisterEvent;
use Xpressengine\Plugins\XeroCommerce\Events\OrderObserver;
use Xpressengine\Plugins\XeroCommerce\Events\ProductOptionItemObserver;
use Xpressengine\Plugins\XeroCommerce\Handlers\OrderHandler;
use Xpressengine\Plugins\XeroCommerce\Handlers\ProductOptionItemHandler;
use Xpressengine\Plugins\XeroCommerce\Models\Order;
use Xpressengine\Plugins\XeroCommerce\Models\OrderItemGroup;
use Xpressengine\Plugins\XeroCommerce\Models\ProductOptionItem;
use Xpressengine\Plugins\XeroCommerce\Notifications\StockLack;

class EventManager
{
    public static function listenEvents()
    {
        self::newRegisterProductListen();
        self::checkProductOptionStockListen();
        Order::observe(OrderObserver::class);
        ProductOptionItem::observe(ProductOptionItemObserver::class);
    }

    public static function newRegisterProductListen()
    {
        \Event::listen(NewProductRegisterEvent::class, function ($productData) {
            \Log::info($productData->product->id);
        });
    }

    public static function checkProductOptionStockListen()
    {
        intercept(
            sprintf('%s@update', ProductOptionItemHandler::class),
            'XeroCommerceProductOptionItemUpdate',
            function ($function, $optionItem, $args) {

                return $function($optionItem, $args);
            }
        );
    }
}
