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

interface MinimumAmountSettingsStore
{
    public function read(): MinimumAmountSettings;

    public function save(MinimumAmountSettings $settings): void;
}
