<?php
namespace Tygh\Sdk\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Tygh\Sdk\Commands\Traits\ValidateCartPathTrait;
use Tygh\Sdk\Entities\Addon;

class AddonRemoveCommand extends Command
{
    use ValidateCartPathTrait;
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('addon:remove')
            ->setDescription(
                'Removes all add-on files in specified cart directory'
            )
            ->addArgument('name',
                InputArgument::REQUIRED,
                'Add-on ID (name)'
            )
            ->addArgument('cart-directory',
                InputArgument::REQUIRED,
                'Path to CS-Cart installation directory'
            );
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fs = new Filesystem();

        $addon_id = $input->getArgument('name');
        $abs_cart_path = rtrim(realpath($input->getArgument('cart-directory')), '\\/') . '/';

        $this->validateCartPath($abs_cart_path, $input, $output);

        $addon = new Addon($addon_id, $abs_cart_path);
        $addon_files_glob_masks = $addon->getFilesGlobMasks();

        $glob_matches = $addon->matchFilesAgainstGlobMasks($addon_files_glob_masks, $abs_cart_path);

        $counter = 0;
        foreach ($glob_matches as $rel_filepath) {
            $abs_cart_filepath = $abs_cart_path . $rel_filepath;
            $fs->remove($abs_cart_filepath);

            $counter++;
            $output->writeln('<info>OK</info>');
        }

        $output->writeln(sprintf('<options=bold>%u</> <info>files and directories have been removed.</info>', $counter));
    }
}