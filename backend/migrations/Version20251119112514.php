<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251119112514 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update creditors table: remove inn, ogrn, type fields and add head_full_name, bank_details fields';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE creditors DROP inn');
        $this->addSql('ALTER TABLE creditors DROP ogrn');
        $this->addSql('ALTER TABLE creditors DROP type');
        $this->addSql('ALTER TABLE creditors ADD head_full_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE creditors ADD bank_details TEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE creditors DROP head_full_name');
        $this->addSql('ALTER TABLE creditors DROP bank_details');
        $this->addSql('ALTER TABLE creditors ADD inn VARCHAR(12) DEFAULT NULL');
        $this->addSql('ALTER TABLE creditors ADD ogrn VARCHAR(15) DEFAULT NULL');
        $this->addSql('ALTER TABLE creditors ADD type VARCHAR(50) DEFAULT NULL');
    }
}
