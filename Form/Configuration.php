<?php

namespace OrderMinimumAmount\Form;

use OrderMinimumAmount\OrderMinimumAmount;
use Thelia\Core\Translation\Translator;
use Thelia\Form\BaseForm;

class Configuration extends BaseForm
{
    protected function buildForm()
    {
        $form = $this->formBuilder;

        $fields = [
            ["name" => 'minimum_amount', "label" => "Minimum amount for order"],
        ];

        foreach ($fields as $field) {
            $configQuery = OrderMinimumAmount::getConfigValue($field['name'], "");
            $type = 'text';
            if (array_key_exists('type', $field)) {
                $type = $field['type'];
            }
            $form->add(
                $field['name'],
                $type,
                [
                    'data' => $configQuery,
                    'label' => Translator::getInstance()->trans($field['label'], [], OrderMinimumAmount::DOMAIN_NAME),
                    'label_attr' => [
                        'for' => $field['name']
                    ],
                ]
            );
        }
    }

    public function getName()
    {
        return "order_minimum_amount_configuration_form";
    }
}