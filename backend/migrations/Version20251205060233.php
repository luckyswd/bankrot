<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251205060233 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contracts ADD procedure_initiation_rosgvardia_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE contracts ADD CONSTRAINT FK_950A9736DEC201C FOREIGN KEY (procedure_initiation_rosgvardia_id) REFERENCES rosgvardia (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_950A9736DEC201C ON contracts (procedure_initiation_rosgvardia_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contracts DROP FOREIGN KEY FK_950A9736DEC201C');
        $this->addSql('DROP INDEX IDX_950A9736DEC201C ON contracts');
        $this->addSql('ALTER TABLE contracts DROP procedure_initiation_rosgvardia_id');
    }
}
