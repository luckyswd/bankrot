<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251231100416 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Replace ManyToMany creditors relation with OneToMany preCourtCreditors relation';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE contracts_pre_court_creditors (created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, id INT AUTO_INCREMENT NOT NULL, contract_id INT NOT NULL, creditor_id INT NOT NULL, credit_contract_number VARCHAR(255) DEFAULT NULL, credit_contract_date DATE DEFAULT NULL, debt_amount VARCHAR(255) DEFAULT NULL, principal_amount VARCHAR(255) DEFAULT NULL, financial_sanctions VARCHAR(255) DEFAULT NULL, INDEX IDX_PRE_COURT_CONTRACT (contract_id), INDEX IDX_PRE_COURT_CREDITOR (creditor_id), UNIQUE INDEX contract_creditor_pre_court_unique (contract_id, creditor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE contracts_pre_court_creditors ADD CONSTRAINT FK_PRE_COURT_CONTRACT FOREIGN KEY (contract_id) REFERENCES contracts (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contracts_pre_court_creditors ADD CONSTRAINT FK_PRE_COURT_CREDITOR FOREIGN KEY (creditor_id) REFERENCES creditors (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE IF EXISTS contracts_creditors');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE contracts_creditors (contracts_id INT NOT NULL, creditor_id INT NOT NULL, INDEX IDX_18D3991024584564 (contracts_id), INDEX IDX_18D39910DF91AC92 (creditor_id), PRIMARY KEY(contracts_id, creditor_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE contracts_creditors ADD CONSTRAINT FK_18D3991024584564 FOREIGN KEY (contracts_id) REFERENCES contracts (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contracts_creditors ADD CONSTRAINT FK_18D39910DF91AC92 FOREIGN KEY (creditor_id) REFERENCES creditors (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contracts_pre_court_creditors DROP FOREIGN KEY FK_PRE_COURT_CONTRACT');
        $this->addSql('ALTER TABLE contracts_pre_court_creditors DROP FOREIGN KEY FK_PRE_COURT_CREDITOR');
        $this->addSql('DROP TABLE contracts_pre_court_creditors');
    }
}
