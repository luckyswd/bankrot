<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251228171509 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE financial_managers (created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, id INT AUTO_INCREMENT NOT NULL, fio VARCHAR(255) DEFAULT NULL, inn VARCHAR(12) DEFAULT NULL, snils VARCHAR(14) DEFAULT NULL, arbitration_manager_registry_number VARCHAR(50) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, phone VARCHAR(20) DEFAULT NULL, aau_name VARCHAR(255) DEFAULT NULL, aau_ogrn VARCHAR(15) DEFAULT NULL, aau_inn VARCHAR(12) DEFAULT NULL, aau_address LONGTEXT DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE contracts ADD financial_manager_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE contracts ADD CONSTRAINT FK_950A973FD5CC44A FOREIGN KEY (financial_manager_id) REFERENCES financial_managers (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_950A973FD5CC44A ON contracts (financial_manager_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE financial_managers');
        $this->addSql('ALTER TABLE contracts DROP FOREIGN KEY FK_950A973FD5CC44A');
        $this->addSql('DROP INDEX IDX_950A973FD5CC44A ON contracts');
        $this->addSql('ALTER TABLE contracts DROP financial_manager_id');
    }
}
