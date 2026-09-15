<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260915120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Шаблоны документов хранятся в src/document-templates: пути загруженных шаблонов переводятся из var/document-templates';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE document_templates SET path = REPLACE(path, '/var/document-templates/', '/src/document-templates/') WHERE path LIKE '%/var/document-templates/%'");
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('Шаблоны, загруженные после переноса, есть только в src/document-templates, вернуть их пути в var нельзя');
    }
}
