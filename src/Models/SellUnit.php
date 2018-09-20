<?php


namespace Xpressengine\Plugins\XeroCommerce\Models;

use Xpressengine\Database\Eloquent\DynamicModel;

abstract class SellUnit extends DynamicModel
{
    public function cartGroup()
    {
        return $this->morphMany(CartGroup::class, 'unit');
    }

    public function orderItemGroup()
    {
        return $this->morphMany(OrderItemGroup::class, 'unit');
    }

    abstract public function sellType();

    abstract public function getName();

    abstract public function getInfo();

    abstract public function getOriginalPrice();

    abstract public function getSellPrice();

    public function getDiscountPrice()
    {
        return $this->getOriginalPrice()-$this->getSellPrice();
    }
}
