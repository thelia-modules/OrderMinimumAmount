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

namespace OrderMinimumAmount\Step;

use OrderMinimumAmount\Exception\MinimumAmountNotReachedException;
use OrderMinimumAmount\Service\MinimumAmountResolver;
use Thelia\Domain\Checkout\Service\Step\CheckoutStepProviderInterface;
use Thelia\Model\Cart;

/**
 * The minimum as a check of the checkout, with no screen of its own.
 *
 * Skipped for every cart, so that it never shows in the tunnel, and checked anyway when
 * the order is placed, by the theme as by the front API (CheckoutValidationService
 * ignores isSkippedFor() on purpose). A theme sending the buyer back to the first
 * incomplete step finds none and lands on the cart, which is where the cart total can be
 * changed.
 */
final readonly class MinimumAmountStepProvider implements CheckoutStepProviderInterface
{
    public const CODE = 'order_minimum_amount';

    public function __construct(
        private MinimumAmountResolver $resolver,
    ) {
    }

    public function code(): string
    {
        return self::CODE;
    }

    public function defaultPosition(): int
    {
        return 2;
    }

    public function isMandatory(): bool
    {
        return true;
    }

    public function isSkippedFor(Cart $cart): bool
    {
        return true;
    }

    public function check(Cart $cart): void
    {
        $minimumAmount = $this->resolver->forCart($cart);

        if (!$minimumAmount->isReached()) {
            throw new MinimumAmountNotReachedException($minimumAmount);
        }
    }

    public function componentName(): ?string
    {
        return null;
    }
}
