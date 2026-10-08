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

namespace OrderMinimumAmount\Model;

/**
 * The minimum that applies to one cart, next to the total it is compared with.
 *
 * Amounts are decimal strings with two decimals, compared in cents so that 249.999
 * rounded by the cart never passes for 250.
 */
final readonly class MinimumAmount
{
    public function __construct(
        public string $minimumAmount,
        public string $cartTotal,
        public bool $taxesIncluded,
        public string $currencyCode,
    ) {
    }

    public function isReached(): bool
    {
        return self::cents($this->cartTotal) >= self::cents($this->minimumAmount);
    }

    public function remainingAmount(): string
    {
        return number_format(max(0, self::cents($this->minimumAmount) - self::cents($this->cartTotal)) / 100, 2, '.', '');
    }

    private static function cents(string $amount): int
    {
        return (int) round((float) $amount * 100);
    }
}
