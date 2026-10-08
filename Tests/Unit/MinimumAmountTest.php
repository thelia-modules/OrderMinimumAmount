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

namespace OrderMinimumAmount\Tests\Unit;

use OrderMinimumAmount\Event\CustomerMinimumAmountEvent;
use OrderMinimumAmount\Exception\MinimumAmountNotReachedException;
use OrderMinimumAmount\Model\MinimumAmountSettings;
use OrderMinimumAmount\Service\MinimumAmountResolver;
use OrderMinimumAmount\Service\MinimumAmountSettingsStore;
use OrderMinimumAmount\Step\MinimumAmountStepProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\RequestStack;
use Thelia\Core\Translation\Translator;
use Thelia\Domain\Taxation\TaxEngine\TaxEngine;
use Thelia\Model\Cart;
use Thelia\Model\Country;
use Thelia\Model\Currency;
use Thelia\Model\Customer;

final class MinimumAmountTest extends TestCase
{
    private EventDispatcher $dispatcher;

    protected function setUp(): void
    {
        new Translator(new RequestStack());
        $this->dispatcher = new EventDispatcher();
    }

    #[Test]
    public function aCartBelowTheMinimumExcludingTaxesIsRefused(): void
    {
        $provider = $this->stepProvider(MinimumAmountSettings::fromInput('250', false));

        try {
            $provider->check($this->cart(totalExcludingTaxes: 240.0, totalIncludingTaxes: 288.0));
            self::fail('A cart of 240 excluding taxes is below a minimum of 250 excluding taxes.');
        } catch (MinimumAmountNotReachedException $refusal) {
            self::assertSame('order-minimum-amount-not-reached', $refusal->violationCode());
            self::assertSame(
                ['minimumAmount' => '250.00', 'cartTotal' => '240.00', 'remainingAmount' => '10.00', 'taxesIncluded' => false],
                $refusal->violationDetails(),
            );
        }
    }

    #[Test]
    public function aCartAboveTheMinimumExcludingTaxesIsAccepted(): void
    {
        $provider = $this->stepProvider(MinimumAmountSettings::fromInput('250', false));

        $provider->check($this->cart(totalExcludingTaxes: 260.0, totalIncludingTaxes: 312.0));

        $this->addToAssertionCount(1);
    }

    #[Test]
    public function theTotalIncludingTaxesIsComparedWhenTheShopSaysSo(): void
    {
        $provider = $this->stepProvider(MinimumAmountSettings::fromInput('250', true));

        // 210 excluding taxes, 252 including: accepted taxes included, refused excluded.
        $provider->check($this->cart(totalExcludingTaxes: 210.0, totalIncludingTaxes: 252.0));

        $this->expectException(MinimumAmountNotReachedException::class);
        $this->stepProvider(MinimumAmountSettings::fromInput('250', false))
            ->check($this->cart(totalExcludingTaxes: 210.0, totalIncludingTaxes: 252.0));
    }

    #[Test]
    public function theMinimumOfTheCustomerReplacesTheGlobalOne(): void
    {
        $this->dispatcher->addListener(
            CustomerMinimumAmountEvent::NAME,
            static function (CustomerMinimumAmountEvent $event): void {
                self::assertSame('250.00', $event->globalMinimumAmount);
                $event->setMinimumAmount('180.5');
            },
        );
        $resolver = $this->resolver(MinimumAmountSettings::fromInput('250', false));

        $minimumAmount = $resolver->forCart($this->cart(totalExcludingTaxes: 200.0, totalIncludingTaxes: 240.0, customer: new Customer()));

        self::assertSame('180.50', $minimumAmount->minimumAmount);
        self::assertTrue($minimumAmount->isReached());
    }

    #[Test]
    public function theGlobalMinimumAppliesWhenNoModuleAnswers(): void
    {
        $resolver = $this->resolver(MinimumAmountSettings::fromInput('250', false));

        $minimumAmount = $resolver->forCart($this->cart(totalExcludingTaxes: 200.0, totalIncludingTaxes: 240.0, customer: new Customer()));

        self::assertSame('250.00', $minimumAmount->minimumAmount);
        self::assertSame('50.00', $minimumAmount->remainingAmount());
    }

    #[Test]
    public function aCustomerMinimumOfZeroLetsAnyCartThrough(): void
    {
        $this->dispatcher->addListener(
            CustomerMinimumAmountEvent::NAME,
            static fn (CustomerMinimumAmountEvent $event) => $event->setMinimumAmount('0'),
        );

        $this->stepProvider(MinimumAmountSettings::fromInput('250', false))
            ->check($this->cart(totalExcludingTaxes: 12.0, totalIncludingTaxes: 14.4, customer: new Customer()));

        $this->addToAssertionCount(1);
    }

    #[Test]
    public function aCartWithoutCustomerDispatchesNothing(): void
    {
        $this->dispatcher->addListener(
            CustomerMinimumAmountEvent::NAME,
            static fn () => self::fail('No customer, no minimum of a customer to ask for.'),
        );

        $minimumAmount = $this->resolver(MinimumAmountSettings::fromInput('250', false))
            ->forCart($this->cart(totalExcludingTaxes: 100.0, totalIncludingTaxes: 120.0));

        self::assertSame('250.00', $minimumAmount->minimumAmount);
    }

    #[Test]
    public function aStoredValueThatIsNotAnAmountReadsAsNoMinimum(): void
    {
        self::assertSame('0.00', MinimumAmountSettings::fromStoredValues('abc', null)->minimumAmount);
        self::assertSame('250.00', MinimumAmountSettings::fromStoredValues('250', null)->minimumAmount);
        self::assertTrue(MinimumAmountSettings::fromStoredValues('250', null)->taxesIncluded);
    }

    #[Test]
    public function anAmountThatIsNotAnAmountIsRefusedAtTheSetting(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        MinimumAmountSettings::fromInput('-10', false);
    }

    private function stepProvider(MinimumAmountSettings $settings): MinimumAmountStepProvider
    {
        return new MinimumAmountStepProvider($this->resolver($settings));
    }

    private function resolver(MinimumAmountSettings $settings): MinimumAmountResolver
    {
        $store = $this->createStub(MinimumAmountSettingsStore::class);
        $store->method('read')->willReturn($settings);

        $taxEngine = $this->createStub(TaxEngine::class);
        $taxEngine->method('getDeliveryCountry')->willReturn(new Country());

        return new MinimumAmountResolver($store, $taxEngine, $this->dispatcher);
    }

    private function cart(float $totalExcludingTaxes, float $totalIncludingTaxes, ?Customer $customer = null): Cart
    {
        $currency = new Currency();
        $currency->setCode('EUR');

        $cart = $this->createStub(Cart::class);
        $cart->method('getTotalAmount')->willReturn($totalExcludingTaxes);
        $cart->method('getTaxedAmount')->willReturn($totalIncludingTaxes);
        $cart->method('getCustomer')->willReturn($customer);
        $cart->method('getCurrency')->willReturn($currency);

        return $cart;
    }
}
