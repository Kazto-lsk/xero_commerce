<?php

namespace Xpressengine\Plugins\XeroCommerce\Models;


use Xpressengine\Database\Eloquent\DynamicModel;

class OrderDelivery extends DynamicModel
{
    protected $table='xero_commerce_order_delivery';

    const READY = 1;
    const PROCESSING = 2;
    const DONE = 3;
    const BACK = 4;
    const STATUS = [
        '준비중', '배송중', '완료', '반송'
    ];
}
