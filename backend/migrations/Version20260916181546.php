<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260916181546 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Имущество должника: недвижимое и движимое';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE contracts_property (created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, id INT AUTO_INCREMENT NOT NULL, subtype VARCHAR(50) NOT NULL, name VARCHAR(500) NOT NULL, ownership_type VARCHAR(255) DEFAULT NULL, location VARCHAR(500) DEFAULT NULL, area VARCHAR(50) DEFAULT NULL, identification_number VARCHAR(255) DEFAULT NULL, pledge_info VARCHAR(500) DEFAULT NULL, manager_valuation NUMERIC(15, 2) DEFAULT NULL, appraiser_valuation NUMERIC(15, 2) DEFAULT NULL, is_excluded_from_estate TINYINT(1) DEFAULT NULL, exclusion_reason VARCHAR(500) DEFAULT NULL, excluded_valuation NUMERIC(15, 2) DEFAULT NULL, contract_id INT NOT NULL, INDEX IDX_DDC4E2762576E0FD (contract_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE contracts_property ADD CONSTRAINT FK_DDC4E2762576E0FD FOREIGN KEY (contract_id) REFERENCES contracts (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE contracts_property DROP FOREIGN KEY FK_DDC4E2762576E0FD');
        $this->addSql('DROP TABLE contracts_property');
    }
}
