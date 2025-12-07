<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251206165306 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("
                INSERT INTO document_templates (id, path, name, category) 
                VALUES (
                    200, 
                    '/var/www/html/src/document-templates/9_sov_kom_bank.xlsx', 
                    '9. Заявка на открытие спецсчета в Совкомбанк', 
                    'judicial_procedure_initiation'
                )
            ");
    }

    public function down(Schema $schema): void
    {
    }
}
