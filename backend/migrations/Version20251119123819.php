<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251119123819 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update FNS table: remove code field, add director_name and bank_details fields';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE fns DROP COLUMN code');
        $this->addSql('ALTER TABLE fns ADD director_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE fns ADD bank_details TEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE fns DROP COLUMN director_name');
        $this->addSql('ALTER TABLE fns DROP COLUMN bank_details');
        $this->addSql('ALTER TABLE fns ADD code VARCHAR(50) DEFAULT NULL');
    }
}
