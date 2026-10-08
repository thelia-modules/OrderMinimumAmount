<?php

declare(strict_types=1);

/*
 * This file is part of the Thelia package.
 * http://www.thelia.net
 *
 * (c) OpenStudio <info@thelia.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace OrderMinimumAmount\Service;

use OrderMinimumAmount\Event\CustomerMinimumAmountEvent;
use OrderMinimumAmount\Model\MinimumAmount;
use OrderMinimumAmount\Model\MinimumAmountSettings;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Service\ResetInterface;
use Thelia\Domain\Taxation\TaxEngine\TaxEngine;
use Thelia\Model\Cart;
use Thelia\Model\Currency;

/**
 * The minimum a cart has to reach and how far it is from it, for the checkout and for
 * the screens alike, so that both always say the same thing.
 *
 * The total is the cart without postage, discounts deducted, with or without taxes as
 * the shop is configured. The minimum of a customer is asked once per request.
 */
final class MinimumAmountResolver implements ResetInterface
{
    /** @var array<string, string> */
    private array $customerMinimums = [];

    public function __construct(
        private readonly MinimumAmountSettingsStore $settingsStore,
        private readonly TaxEngine $taxEngine,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }

    public function forCart(Cart $cart): MinimumAmount
    {
        $settings = $this->settingsStore->read();

        $total = $settings->taxesIncluded
            ? $cart->getTaxedAmount($this->taxEngine->getDeliveryCountry())
            : $cart->getTotalAmount();

        return new MinimumAmount(
            $this->minimumOf($cart, $settings),
            number_format($total, 2, '.', ''),
            $settings->taxesIncluded,
            $cart->getCurrency()?->getCode() ?? Currency::getDefaultCurrency()->getCode(),
        );
    }

    public function reset(): void
    {
        $this->customerMinimums = [];
    }

    private function minimumOf(Cart $cart, MinimumAmountSettings $settings): string
    {
        $customer = $cart->getCustomer();

        if (null === $customer) {
            return $settings->minimumAmount;
        }

        $key = $customer->getId().'|'.$settings->minimumAmount;

        if (isset($this->customerMinimums[$key])) {
            return $this->customerMinimums[$key];
        }

        $event = new CustomerMinimumAmountEvent($customer, $cart, $settings->minimumAmount);
        $this->dispatcher->dispatch($event, CustomerMinimumAmountEvent::NAME);

        $customerMinimum = $event->getMinimumAmount();

        return $this->customerMinimums[$key] = null === $customerMinimum
            ? $settings->minimumAmount
            : MinimumAmountSettings::normalizedAmount($customerMinimum);
    }
}
