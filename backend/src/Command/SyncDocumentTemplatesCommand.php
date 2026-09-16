<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\Templates\DocumentTemplateRegistrar;
use App\Service\Templates\DocumentTemplateSynchronizer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:document-templates:sync',
    description: 'Раскладывает шаблоны документов из образа в src/document-templates и регистрирует их в базе',
)]
class SyncDocumentTemplatesCommand extends Command
{
    public function __construct(
        private readonly DocumentTemplateSynchronizer $documentTemplateSynchronizer,
        private readonly DocumentTemplateRegistrar $documentTemplateRegistrar,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $files = $this->documentTemplateSynchronizer->synchronize();
        $records = $this->documentTemplateRegistrar->register();

        $style = new SymfonyStyle($input, $output);
        $style->success(sprintf(
            'Файлов из образа: %d, перенесено из var/document-templates: %d',
            $files['bundled'],
            $files['migrated'],
        ));
        $style->success(sprintf(
            'Записей создано: %d, обновлено: %d, удалено: %d',
            $records['created'],
            $records['updated'],
            $records['removed'],
        ));

        return Command::SUCCESS;
    }
}
