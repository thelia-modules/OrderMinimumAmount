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

namespace OrderMinimumAmount\Form;

use OrderMinimumAmount\OrderMinimumAmount;
use OrderMinimumAmount\Service\ModuleConfigSettingsStore;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Thelia\Core\Translation\Translator;
use Thelia\Form\BaseForm;

class ConfigurationForm extends BaseForm
{
    public static function getName(): string
    {
        return 'order_minimum_amount_configuration';
    }

    protected function buildForm(): void
    {
        $settings = (new ModuleConfigSettingsStore())->read();
        $translator = Translator::getInstance();

        $this->formBuilder
            ->add('minimum_amount', TextType::class, [
                'data' => $settings->minimumAmount,
                'label' => $translator->trans('Minimum amount of an order', [], OrderMinimumAmount::DOMAIN_NAME),
                'help' => $translator->trans('Postage excluded, discounts deducted. 0: no minimum.', [], OrderMinimumAmount::DOMAIN_NAME),
                'constraints' => [
                    new NotBlank(),
                    new Regex(
                        pattern: '/^\d+([.,]\d{1,2})?$/',
                        message: $translator->trans('A positive amount with at most two decimals is expected.', [], OrderMinimumAmount::DOMAIN_NAME),
                    ),
                ],
            ])
            ->add('taxes_included', ChoiceType::class, [
                'data' => $settings->taxesIncluded ? '1' : '0',
                'label' => $translator->trans('Cart total compared with the minimum', [], OrderMinimumAmount::DOMAIN_NAME),
                'choices' => [
                    $translator->trans('Including taxes', [], OrderMinimumAmount::DOMAIN_NAME) => '1',
                    $translator->trans('Excluding taxes', [], OrderMinimumAmount::DOMAIN_NAME) => '0',
                ],
                'constraints' => [new NotBlank()],
            ]);
    }
}
