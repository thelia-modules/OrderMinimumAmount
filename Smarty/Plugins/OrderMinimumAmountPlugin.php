<?php

namespace OrderMinimumAmount\Smarty\Plugins;

use OrderMinimumAmount\OrderMinimumAmount;
use TheliaSmarty\Template\AbstractSmartyPlugin;
use TheliaSmarty\Template\SmartyPluginDescriptor;

class OrderMinimumAmountPlugin extends AbstractSmartyPlugin
{
    public function getPluginDescriptors()
    {
        return [
            new SmartyPluginDescriptor("function", "OrderMinimumAmount", $this, "getOrderMinimumAmount"),
        ];
    }

    public function getOrderMinimumAmount()
    {
        return OrderMinimumAmount::getConfigValue('minimum_amount');
    }
}