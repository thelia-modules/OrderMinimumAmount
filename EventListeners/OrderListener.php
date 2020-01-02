<?php

namespace OrderMinimumAmount\EventListeners;

use OrderMinimumAmount\OrderMinimumAmount;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Thelia\Core\Event\Order\OrderEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\HttpFoundation\Request;
use Thelia\Core\Translation\Translator;
use Thelia\TaxEngine\TaxEngine;

class OrderListener implements EventSubscriberInterface
{
    /** @var Request|null  */
    protected $request;

    protected $taxEngine;

    public function __construct(
        RequestStack $requestStack,
        TaxEngine $taxEngine
    ) {
        $this->request = $requestStack->getCurrentRequest();
        $this->taxEngine = $taxEngine;
    }

    public static function getSubscribedEvents()
    {
        return [
            TheliaEvents::ORDER_PAY => ['checkOrderMinimumAmount', 128],
        ];
    }

    public function checkOrderMinimumAmount(OrderEvent $event)
    {
        $cart = $this->request->getSession()->getSessionCart();
        $total = $cart->getTaxedAmount($taxCountry = $this->taxEngine->getDeliveryCountry());

        $orderMinimumAmount = OrderMinimumAmount::getConfigValue("minimum_amount", 0);

        if ($total < $orderMinimumAmount) {
            $event->stopPropagation();
            throw new \Exception(Translator::getInstance()->trans("Minimum amount not reached", [], OrderMinimumAmount::DOMAIN_NAME));
        }
    }
}