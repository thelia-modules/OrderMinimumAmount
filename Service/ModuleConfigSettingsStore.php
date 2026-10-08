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

use OrderMinimumAmount\Model\MinimumAmountSettings;
use OrderMinimumAmount\OrderMinimumAmount;

/**
 * Settings kept in the module configuration. The minimum keeps the key of the
 * Thelia 2 versions, so that an upgraded shop keeps its amount; a shop that never set
 * the comparison mode compares taxes included, as those versions did.
 */
final readonly class ModuleConfigSettingsStore implements MinimumAmountSettingsStore
{
    public function read(): MinimumAmountSettings
    {
        return MinimumAmountSettings::fromStoredValues(
            OrderMinimumAmount::getConfigValue(OrderMinimumAmount::CONFIG_MINIMUM_AMOUNT),
            OrderMinimumAmount::getConfigValue(OrderMinimumAmount::CONFIG_TAXES_INCLUDED),
        );
    }

    public function save(MinimumAmountSettings $settings): void
    {
        OrderMinimumAmount::setConfigValue(OrderMinimumAmount::CONFIG_MINIMUM_AMOUNT, $settings->minimumAmount);
        OrderMinimumAmount::setConfigValue(OrderMinimumAmount::CONFIG_TAXES_INCLUDED, $settings->taxesIncluded ? '1' : '0');
    }
}
