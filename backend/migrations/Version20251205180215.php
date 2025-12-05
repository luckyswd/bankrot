<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251205180215 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE contracts_creditors_claim (created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, id INT AUTO_INCREMENT NOT NULL, debt_amount VARCHAR(255) DEFAULT NULL, principal_amount VARCHAR(255) DEFAULT NULL, interest VARCHAR(255) DEFAULT NULL, penalty VARCHAR(255) DEFAULT NULL, late_fee VARCHAR(255) DEFAULT NULL, forfeiture VARCHAR(255) DEFAULT NULL, state_duty VARCHAR(255) DEFAULT NULL, basis JSON DEFAULT NULL, inclusion TINYINT(1) DEFAULT NULL, contract_id INT NOT NULL, creditor_id INT NOT NULL, INDEX IDX_D44591FE2576E0FD (contract_id), INDEX IDX_D44591FEDF91AC92 (creditor_id), UNIQUE INDEX contract_creditor_claim_unique (contract_id, creditor_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE contracts_creditors_claim ADD CONSTRAINT FK_D44591FE2576E0FD FOREIGN KEY (contract_id) REFERENCES contracts (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contracts_creditors_claim ADD CONSTRAINT FK_D44591FEDF91AC92 FOREIGN KEY (creditor_id) REFERENCES creditors (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contracts DROP procedure_main_amount');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contracts_creditors_claim DROP FOREIGN KEY FK_D44591FE2576E0FD');
        $this->addSql('ALTER TABLE contracts_creditors_claim DROP FOREIGN KEY FK_D44591FEDF91AC92');
        $this->addSql('DROP TABLE contracts_creditors_claim');
        $this->addSql('ALTER TABLE contracts ADD procedure_main_amount VARCHAR(255) DEFAULT NULL');
    }
}
