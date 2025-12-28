<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251228170759 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contracts DROP FOREIGN KEY `FK_950A973783E3463`');
        $this->addSql('DROP INDEX IDX_950A973783E3463 ON contracts');
        $this->addSql('ALTER TABLE contracts CHANGE manager_id financial_manager_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE contracts ADD CONSTRAINT FK_950A973FD5CC44A FOREIGN KEY (financial_manager_id) REFERENCES financial_managers (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_950A973FD5CC44A ON contracts (financial_manager_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contracts DROP FOREIGN KEY FK_950A973FD5CC44A');
        $this->addSql('DROP INDEX IDX_950A973FD5CC44A ON contracts');
        $this->addSql('ALTER TABLE contracts CHANGE financial_manager_id manager_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE contracts ADD CONSTRAINT `FK_950A973783E3463` FOREIGN KEY (manager_id) REFERENCES financial_managers (id) ON UPDATE NO ACTION ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_950A973783E3463 ON contracts (manager_id)');
    }
}
