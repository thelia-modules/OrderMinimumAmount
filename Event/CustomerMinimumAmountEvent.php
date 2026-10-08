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

namespace OrderMinimumAmount\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Thelia\Model\Cart;
use Thelia\Model\Customer;

/**
 * Asked once per customer and per request, when the cart belongs to a customer: a
 * module that knows a minimum of its own for this customer answers it here, and it
 * replaces the global minimum of the shop. Nobody answering means the global minimum.
 *
 * The amount is compared in the mode the shop is configured with (taxes included or
 * not), as a decimal string with at most two decimals. "0" means no minimum
 * for this customer. Several listeners may answer: the last one to set it wins, so a
 * lower priority overrides a higher one.
 */
final class CustomerMinimumAmountEvent extends Event
{
    public const NAME = 'order_minimum_amount.customer_minimum_amount';

    private ?string $minimumAmount = null;

    public function __construct(
        public readonly Customer $customer,
        public readonly Cart $cart,
        public readonly string $globalMinimumAmount,
    ) {
    }

    public function setMinimumAmount(string $minimumAmount): void
    {
        $this->minimumAmount = $minimumAmount;
    }

    public function getMinimumAmount(): ?string
    {
        return $this->minimumAmount;
    }
}
