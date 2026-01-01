<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260101124046 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add state_duty_for_consideration column to contracts_creditors_claim table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE contracts_creditors_claim ADD state_duty_for_consideration VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE contracts_creditors_claim DROP state_duty_for_consideration');
    }
}
