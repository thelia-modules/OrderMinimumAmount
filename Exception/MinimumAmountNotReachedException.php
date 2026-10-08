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

namespace OrderMinimumAmount\Exception;

use OrderMinimumAmount\Model\MinimumAmount;
use OrderMinimumAmount\OrderMinimumAmount;
use Thelia\Core\Translation\Translator;
use Thelia\Domain\Checkout\Exception\CheckoutException;

final class MinimumAmountNotReachedException extends CheckoutException
{
    public const VIOLATION_CODE = 'order-minimum-amount-not-reached';

    public function __construct(public readonly MinimumAmount $minimumAmount)
    {
        $translator = Translator::getInstance();
        $formatter = new \NumberFormatter($translator->getLocale(), \NumberFormatter::CURRENCY);

        parent::__construct($translator->trans(
            $minimumAmount->taxesIncluded
                ? 'The minimum order amount is %minimum% including taxes: add %remaining% to your cart.'
                : 'The minimum order amount is %minimum% excluding taxes: add %remaining% to your cart.',
            [
                '%minimum%' => $formatter->formatCurrency((float) $minimumAmount->minimumAmount, $minimumAmount->currencyCode),
                '%remaining%' => $formatter->formatCurrency((float) $minimumAmount->remainingAmount(), $minimumAmount->currencyCode),
            ],
            OrderMinimumAmount::DOMAIN_NAME,
        ));
    }

    public function violationCode(): string
    {
        return self::VIOLATION_CODE;
    }

    /**
     * @return array{minimumAmount: string, cartTotal: string, remainingAmount: string, taxesIncluded: bool}
     */
    public function violationDetails(): array
    {
        return [
            'minimumAmount' => $this->minimumAmount->minimumAmount,
            'cartTotal' => $this->minimumAmount->cartTotal,
            'remainingAmount' => $this->minimumAmount->remainingAmount(),
            'taxesIncluded' => $this->minimumAmount->taxesIncluded,
        ];
    }
}
