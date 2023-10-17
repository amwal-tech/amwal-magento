<?php

namespace Amwal\Payments\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReactFiles extends Command
{
    protected function configure()
    {
        $this->setName('amwal:reactfiles')
            ->setDescription('Move react files to pub/static folder');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Start moving react files');

        // Get the base path where modules are stored (either vendor or app/code)
        $basePathVendor = BP . '/vendor/amwal/payments/view/frontend/web/js/react-src/dist';
        $basePathAppCode = BP . '/app/code/Amwal/Payments/view/frontend/web/js/react-src/dist';

        // Determine the module's react-src directory
        $sourceDirectory = is_dir($basePathVendor) ? $basePathVendor : $basePathAppCode;

        // Check if the source directory exists
        if (!is_dir($sourceDirectory)) {
            $output->writeln('Source directory not found.');
            return;
        }

        // Set the destination directory
        $destinationDirectory = BP . '/pub/static/_cache/merged';

        // Check if the destination directory exists, create if not
        if (!is_dir($destinationDirectory)) {
            mkdir($destinationDirectory, 0755, true);
        }

        // Get a list of files in the source directory
        $files = glob("$sourceDirectory/*");

        // Copy each file to the destination directory
        foreach ($files as $file) {
            $destinationFile = $destinationDirectory . '/' . basename($file);
            if (!copy($file, $destinationFile)) {
                $output->writeln("Failed to copy file: $file");
            }
        }

        $output->writeln('End moving react files');
    }
}
