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
 * The minimum every customer has to reach, and whether the cart total it is compared
 * with includes taxes. Amounts are decimal strings with two decimals, as the cart
 * stores them.
 */
final readonly class MinimumAmountSettings
{
    private const AMOUNT_PATTERN = '/^\d+([.,]\d{1,2})?$/';

    private function __construct(
        public string $minimumAmount,
        public bool $taxesIncluded,
    ) {
    }

    /**
     * @throws \InvalidArgumentException when the amount is not a positive decimal with at most two decimals
     */
    public static function fromInput(string $minimumAmount, bool $taxesIncluded): self
    {
        return new self(self::normalizedAmount($minimumAmount), $taxesIncluded);
    }

    /**
     * What a shop configured before this version reads as: a stored value that is not an
     * amount does not block every order, it reads as no minimum.
     */
    public static function fromStoredValues(?string $minimumAmount, ?string $taxesIncluded): self
    {
        $amount = null !== $minimumAmount && 1 === preg_match(self::AMOUNT_PATTERN, trim($minimumAmount))
            ? self::normalizedAmount($minimumAmount)
            : '0.00';

        return new self($amount, '0' !== $taxesIncluded);
    }

    /**
     * @throws \InvalidArgumentException when the amount is not a positive decimal with at most two decimals
     */
    public static function normalizedAmount(string $amount): string
    {
        $amount = trim($amount);

        if (1 !== preg_match(self::AMOUNT_PATTERN, $amount)) {
            throw new \InvalidArgumentException(\sprintf('"%s" is not an amount: a positive number with at most two decimals is expected.', $amount));
        }

        return number_format((float) str_replace(',', '.', $amount), 2, '.', '');
    }
}
