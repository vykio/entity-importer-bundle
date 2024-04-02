<?php

namespace EntityImporterBundle\Command;

use EntityImporterBundle\Component\Importer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImporterCommand extends Command
{
    private Importer $importer;

    protected static string $defaultName = 'app:importer:file';
    protected static string $defaultDescription = 'Import data to app using importer file';

    protected function configure(): void
    {
        $this
            ->addArgument('customer', InputArgument::REQUIRED, 'Customer')
            ->addArgument('file', InputArgument::REQUIRED, 'Import descriptor file path')
        ;
    }

    public function __construct(Importer $importer, string $name = null)
    {
        parent::__construct($name);
        $this->importer = $importer;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->importer->loadFile($input->getArgument('file'));

        var_export($this->importer->getInstances());

        return self::SUCCESS;
    }
}