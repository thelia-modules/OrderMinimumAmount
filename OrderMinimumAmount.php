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

namespace OrderMinimumAmount;

use Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator;
use Thelia\Module\BaseModule;

class OrderMinimumAmount extends BaseModule
{
    public const DOMAIN_NAME = 'orderminimumamount';

    public const BACK_OFFICE_DOMAIN_NAME = 'orderminimumamount.bo.default-twig';

    public const CONFIG_MINIMUM_AMOUNT = 'minimum_amount';

    public const CONFIG_TAXES_INCLUDED = 'taxes_included';

    public static function configureServices(ServicesConfigurator $servicesConfigurator): void
    {
        $servicesConfigurator->load(self::getModuleCode().'\\', __DIR__)
            ->exclude([
                __DIR__.'/I18n/*',
                __DIR__.'/Config/**/*.php',
                __DIR__.'/Tests/*',
                __DIR__.'/Event/*',
                __DIR__.'/Exception/*',
                __DIR__.'/Model/*',
                __DIR__.'/OrderMinimumAmount.php',
            ])
            ->autowire(true)
            ->autoconfigure(true);
    }
}
