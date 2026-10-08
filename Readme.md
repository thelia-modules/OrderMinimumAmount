# Order Minimum Amount

Refuses an order whose cart total is below a minimum amount. Thelia 3.

## Installation

```
composer require thelia/order-minimum-amount-module:^3.0
php bin/console module:refresh
php bin/console module:activate OrderMinimumAmount
```

Version 3 needs Thelia 3. Thelia 2 shops stay on 2.x.

## Settings

Back office, module configuration, or from the console:

```
php bin/console order-minimum-amount:configure --amount=250 --taxes=excluded
php bin/console order-minimum-amount:configure     # prints the current settings
```

- **Minimum amount**: postage excluded, discounts deducted. `0` means no minimum.
- **Cart total compared**: taxes included (default, as in 2.x) or excluded.

A shop upgraded from 2.x keeps its amount (same `minimum_amount` key) and compares
taxes included until the setting is changed.

## How the order is refused

The module declares a checkout step, `order_minimum_amount`, with no screen: it never
shows in the tunnel, and it is checked when the order is placed (theme and front API).
A cart below the minimum is refused with:

- the violation code `order-minimum-amount-not-reached`;
- a translated message giving the minimum and the amount left to add;
- the details `minimumAmount`, `cartTotal`, `remainingAmount`, `taxesIncluded`.

## Twig

`order_minimum_amount()` returns the minimum of the cart in session, or `null` without
a cart. The total is computed once per call, the minimum of a customer once per request.

```twig
{% set minimum = order_minimum_amount() %}
{% if minimum and not minimum.reached %}
    {{ 'Add %amount% to reach the minimum order amount.'|trans({'%amount%': minimum.remainingAmount|format_currency(minimum.currencyCode)}) }}
{% endif %}
```

Fields: `minimumAmount`, `cartTotal`, `remainingAmount`, `taxesIncluded`,
`currencyCode` (amounts as decimal strings with two decimals), `reached`.

## A minimum per customer

Another module gives a customer a minimum of their own by answering
`OrderMinimumAmount\Event\CustomerMinimumAmountEvent`. The event is dispatched only when
the cart belongs to a customer, at most once per customer and per request. Its amount
replaces the global minimum and is compared in the configured mode (taxes included or
not). `0` means no minimum for this customer. Nobody answering means the global minimum.
When several listeners answer, the last one to set the amount wins.

```php
namespace MyModule\EventListener;

use OrderMinimumAmount\Event\CustomerMinimumAmountEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class CustomerMinimumAmountListener implements EventSubscriberInterface
{
    public function __construct(private CustomerMinimumRepository $repository)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [CustomerMinimumAmountEvent::NAME => ['onCustomerMinimumAmount', 128]];
    }

    public function onCustomerMinimumAmount(CustomerMinimumAmountEvent $event): void
    {
        // $event->customer, $event->cart, $event->globalMinimumAmount ("250.00")
        $minimumAmount = $this->repository->minimumOf($event->customer->getId()); // "180.50" or null

        if (null !== $minimumAmount) {
            $event->setMinimumAmount($minimumAmount);
        }
    }
}
```

An amount that is not a positive decimal with at most two decimals is an error.
