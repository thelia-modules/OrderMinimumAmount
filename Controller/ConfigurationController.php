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

namespace OrderMinimumAmount\Controller;

use OrderMinimumAmount\Form\ConfigurationForm;
use OrderMinimumAmount\Model\MinimumAmountSettings;
use OrderMinimumAmount\OrderMinimumAmount;
use OrderMinimumAmount\Service\MinimumAmountSettingsStore;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Form\Exception\FormValidationException;
use Thelia\Tools\URL;

class ConfigurationController extends BaseAdminController
{
    #[Route('/admin/module/order-minimum-amount/configuration', name: 'order_minimum_amount.admin.configuration', methods: ['POST'])]
    public function saveAction(MinimumAmountSettingsStore $settingsStore): Response
    {
        if (null !== $response = $this->checkAuth([AdminResources::MODULE], [OrderMinimumAmount::getModuleCode()], AccessManager::UPDATE)) {
            return $response;
        }

        $form = $this->createForm(ConfigurationForm::getName());

        try {
            $data = $this->validateForm($form)->getData();

            $settingsStore->save(MinimumAmountSettings::fromInput((string) $data['minimum_amount'], '1' === $data['taxes_included']));
        } catch (FormValidationException|\InvalidArgumentException $exception) {
            $this->addFlash('danger', $exception->getMessage());

            return $this->generateRedirect(URL::getInstance()->absoluteUrl('/admin/module/'.OrderMinimumAmount::getModuleCode()));
        }

        $this->addFlash('success', $this->getTranslator()->trans('Settings saved.', [], OrderMinimumAmount::DOMAIN_NAME));

        return $this->generateRedirect(URL::getInstance()->absoluteUrl('/admin/module/'.OrderMinimumAmount::getModuleCode()));
    }
}
