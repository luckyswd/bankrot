<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251104101442 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE users (created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, fio VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_1483A5E9F85E0677 (username), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');

        $this->addSql(
            "INSERT INTO users (username, roles, password, fio, created_at, updated_at)
             VALUES (
               'admin',
               '[\"ROLE_ADMIN\"]',
               '\$2y\$12\$qgA2wXqpcKOyk7qErTnDWuAPtmu.u2/kXWWWgwdW4XYZYNFKiPH2K',
               NULL,
               NOW(),
               NOW()
             )"
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE users');
    }
}
