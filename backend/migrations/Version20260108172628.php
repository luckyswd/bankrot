<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260108172628 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contracts ADD procedure_initiation_report_hearing_date_time DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE contracts_pre_court_creditors RENAME INDEX idx_pre_court_contract TO IDX_E21E8FE72576E0FD');
        $this->addSql('ALTER TABLE contracts_pre_court_creditors RENAME INDEX idx_pre_court_creditor TO IDX_E21E8FE7DF91AC92');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contracts DROP procedure_initiation_report_hearing_date_time');
        $this->addSql('ALTER TABLE contracts_pre_court_creditors RENAME INDEX idx_e21e8fe72576e0fd TO IDX_PRE_COURT_CONTRACT');
        $this->addSql('ALTER TABLE contracts_pre_court_creditors RENAME INDEX idx_e21e8fe7df91ac92 TO IDX_PRE_COURT_CREDITOR');
    }
}
