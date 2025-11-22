<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251122175244 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contracts ADD postal_code VARCHAR(20) DEFAULT NULL');
        $this->addSql('ALTER TABLE courts DROP postal_code, DROP work');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contracts DROP postal_code');
        $this->addSql('ALTER TABLE courts ADD postal_code VARCHAR(10) DEFAULT NULL, ADD work TINYINT(1) DEFAULT NULL');
    }
}
