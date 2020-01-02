<?php

namespace OrderMinimumAmount\Controller;

use OrderMinimumAmount\OrderMinimumAmount;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Core\Translation\Translator;

class ConfigurationController extends BaseAdminController
{
    public function viewAction()
    {
        return $this->render(
            "orderMinimumAmount/configuration"
        );
    }

    public function saveAction()
    {
        if (null !== $response = $this->checkAuth(array(AdminResources::MODULE), 'OrderMinimumAmount', AccessManager::VIEW)) {
            return $response;
        }

        $form = $this->createForm('order_minimum_amount_configuration_form');

        try {
            $data = $this->validateForm($form)->getData();

            OrderMinimumAmount::setConfigValue('minimum_amount', $data['minimum_amount']);
        } catch (\Exception $e) {
            $this->setupFormErrorContext(
                Translator::getInstance()->trans(
                    "Error",
                    [],
                    OrderMinimumAmount::DOMAIN_NAME
                ),
                $e->getMessage(),
                $form
            );
            return $this->viewAction();
        }

        return $this->generateSuccessRedirect($form);
    }
}