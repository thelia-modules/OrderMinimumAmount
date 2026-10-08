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

namespace OrderMinimumAmount\Twig;

use OrderMinimumAmount\Model\MinimumAmount;
use OrderMinimumAmount\Service\MinimumAmountResolver;
use Thelia\Domain\Cart\CartFacade;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * `order_minimum_amount()`: the minimum of the cart in session and what is left to
 * reach it, or null without a cart.
 *
 *     {% set minimum = order_minimum_amount() %}
 *     {% if minimum and not minimum.reached %}{{ minimum.remainingAmount }}{% endif %}
 */
final class MinimumAmountExtension extends AbstractExtension
{
    public function __construct(
        private readonly CartFacade $cartFacade,
        private readonly MinimumAmountResolver $resolver,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('order_minimum_amount', $this->forSessionCart(...)),
        ];
    }

    public function forSessionCart(): ?MinimumAmount
    {
        $cart = $this->cartFacade->getCartFromSession();

        return null === $cart ? null : $this->resolver->forCart($cart);
    }
}
