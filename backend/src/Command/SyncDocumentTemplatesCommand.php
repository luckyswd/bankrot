<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\Templates\DocumentTemplateSynchronizer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:document-templates:sync',
    description: 'Раскладывает шаблоны документов из образа и из старого каталога var в src/document-templates',
)]
class SyncDocumentTemplatesCommand extends Command
{
    public function __construct(
        private readonly DocumentTemplateSynchronizer $documentTemplateSynchronizer,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $result = $this->documentTemplateSynchronizer->synchronize();

        (new SymfonyStyle($input, $output))->success(sprintf(
            'Шаблонов из образа: %d, перенесено из var/document-templates: %d',
            $result['bundled'],
            $result['migrated'],
        ));

        return Command::SUCCESS;
    }
}
