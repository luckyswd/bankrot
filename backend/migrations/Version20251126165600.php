<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251126165600 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contracts ADD procedure_initiation_document_date DATE DEFAULT NULL, ADD procedure_initiation_case_number VARCHAR(255) DEFAULT NULL, ADD procedure_initiation_judge VARCHAR(255) DEFAULT NULL, ADD procedure_initiation_special_account_number VARCHAR(255) DEFAULT NULL, ADD procedure_initiation_decision_date DATE DEFAULT NULL, ADD procedure_initiation_resolution_date DATE DEFAULT NULL, ADD procedure_initiation_mchs_id INT DEFAULT NULL, ADD procedure_initiation_gostekhnadzor_id INT DEFAULT NULL, ADD procedure_initiation_fns_id INT DEFAULT NULL, ADD procedure_initiation_bailiff_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE contracts ADD CONSTRAINT FK_950A9734ACF240B FOREIGN KEY (procedure_initiation_mchs_id) REFERENCES mchs (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE contracts ADD CONSTRAINT FK_950A973CAF39D67 FOREIGN KEY (procedure_initiation_gostekhnadzor_id) REFERENCES gostekhnadzor (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE contracts ADD CONSTRAINT FK_950A973CC5E8C86 FOREIGN KEY (procedure_initiation_fns_id) REFERENCES fns (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE contracts ADD CONSTRAINT FK_950A973E89C3044 FOREIGN KEY (procedure_initiation_bailiff_id) REFERENCES bailiffs (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_950A9734ACF240B ON contracts (procedure_initiation_mchs_id)');
        $this->addSql('CREATE INDEX IDX_950A973CAF39D67 ON contracts (procedure_initiation_gostekhnadzor_id)');
        $this->addSql('CREATE INDEX IDX_950A973CC5E8C86 ON contracts (procedure_initiation_fns_id)');
        $this->addSql('CREATE INDEX IDX_950A973E89C3044 ON contracts (procedure_initiation_bailiff_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contracts DROP FOREIGN KEY FK_950A9734ACF240B');
        $this->addSql('ALTER TABLE contracts DROP FOREIGN KEY FK_950A973CAF39D67');
        $this->addSql('ALTER TABLE contracts DROP FOREIGN KEY FK_950A973CC5E8C86');
        $this->addSql('ALTER TABLE contracts DROP FOREIGN KEY FK_950A973E89C3044');
        $this->addSql('DROP INDEX IDX_950A9734ACF240B ON contracts');
        $this->addSql('DROP INDEX IDX_950A973CAF39D67 ON contracts');
        $this->addSql('DROP INDEX IDX_950A973CC5E8C86 ON contracts');
        $this->addSql('DROP INDEX IDX_950A973E89C3044 ON contracts');
        $this->addSql('ALTER TABLE contracts DROP procedure_initiation_document_date, DROP procedure_initiation_case_number, DROP procedure_initiation_judge, DROP procedure_initiation_special_account_number, DROP procedure_initiation_decision_date, DROP procedure_initiation_resolution_date, DROP procedure_initiation_mchs_id, DROP procedure_initiation_gostekhnadzor_id, DROP procedure_initiation_fns_id, DROP procedure_initiation_bailiff_id');
    }
}
