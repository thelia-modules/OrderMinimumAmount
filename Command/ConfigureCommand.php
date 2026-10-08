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

namespace OrderMinimumAmount\Command;

use OrderMinimumAmount\Model\MinimumAmountSettings;
use OrderMinimumAmount\Service\MinimumAmountSettingsStore;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The settings of the configuration screen, for a deployment that has to set them
 * without clicking: `order-minimum-amount:configure --amount=250 --taxes=excluded`.
 * Without an option, prints the current settings.
 */
#[AsCommand(name: 'order-minimum-amount:configure', description: 'Show or set the minimum order amount and whether the cart total includes taxes')]
final class ConfigureCommand extends Command
{
    public function __construct(
        private readonly MinimumAmountSettingsStore $settingsStore,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('amount', null, InputOption::VALUE_REQUIRED, 'Minimum amount, postage excluded (0: no minimum)')
            ->addOption('taxes', null, InputOption::VALUE_REQUIRED, 'Cart total compared taxes "included" or "excluded"');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $current = $this->settingsStore->read();
        $amount = $input->getOption('amount');
        $taxes = $input->getOption('taxes');

        if (null !== $taxes && !\in_array($taxes, ['included', 'excluded'], true)) {
            $output->writeln('<error>--taxes expects "included" or "excluded".</error>');

            return Command::INVALID;
        }

        if (null !== $amount || null !== $taxes) {
            try {
                $current = MinimumAmountSettings::fromInput(
                    (string) ($amount ?? $current->minimumAmount),
                    null === $taxes ? $current->taxesIncluded : 'included' === $taxes,
                );
            } catch (\InvalidArgumentException $exception) {
                $output->writeln(\sprintf('<error>%s</error>', $exception->getMessage()));

                return Command::INVALID;
            }

            $this->settingsStore->save($current);
        }

        $output->writeln(\sprintf(
            'Minimum order amount: %s, taxes %s.',
            $current->minimumAmount,
            $current->taxesIncluded ? 'included' : 'excluded',
        ));

        return Command::SUCCESS;
    }
}
