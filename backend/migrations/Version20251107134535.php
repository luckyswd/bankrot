<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251107134535 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE contracts (id INT AUTO_INCREMENT NOT NULL, first_name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) DEFAULT NULL, middle_name VARCHAR(255) DEFAULT NULL, is_last_name_changed TINYINT(1) DEFAULT NULL, changed_last_name VARCHAR(255) DEFAULT NULL, birth_date DATE DEFAULT NULL, snils VARCHAR(255) DEFAULT NULL, birth_place VARCHAR(255) DEFAULT NULL, registration_region VARCHAR(255) DEFAULT NULL, registration_district VARCHAR(255) DEFAULT NULL, registration_city VARCHAR(255) DEFAULT NULL, registration_settlement VARCHAR(255) DEFAULT NULL, registration_street VARCHAR(255) DEFAULT NULL, registration_house VARCHAR(50) DEFAULT NULL, registration_building VARCHAR(50) DEFAULT NULL, registration_apartment VARCHAR(50) DEFAULT NULL, passport_series VARCHAR(10) DEFAULT NULL, passport_number VARCHAR(20) DEFAULT NULL, passport_issued_by LONGTEXT DEFAULT NULL, passport_issued_date DATE DEFAULT NULL, passport_department_code VARCHAR(20) DEFAULT NULL, marital_status VARCHAR(50) DEFAULT NULL, spouse_full_name VARCHAR(255) DEFAULT NULL, spouse_birth_date DATE DEFAULT NULL, has_minor_children TINYINT(1) DEFAULT NULL, children JSON DEFAULT NULL, is_student TINYINT(1) DEFAULT NULL, employer_name VARCHAR(255) DEFAULT NULL, employer_address LONGTEXT DEFAULT NULL, employer_inn VARCHAR(12) DEFAULT NULL, social_benefits LONGTEXT DEFAULT NULL, phone VARCHAR(20) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, mailing_address LONGTEXT DEFAULT NULL, debt_amount NUMERIC(15, 2) DEFAULT NULL, has_enforcement_proceedings TINYINT(1) DEFAULT NULL, status VARCHAR(255) NOT NULL, contract_number VARCHAR(255) DEFAULT NULL, contract_date DATE DEFAULT NULL, author_id INT NOT NULL, manager_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_950A973E4EB19B0 (snils), INDEX IDX_950A973F675F31B (author_id), INDEX IDX_950A973783E3463 (manager_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE contracts ADD CONSTRAINT FK_950A973F675F31B FOREIGN KEY (author_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contracts ADD CONSTRAINT FK_950A973783E3463 FOREIGN KEY (manager_id) REFERENCES users (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contracts DROP FOREIGN KEY FK_950A973F675F31B');
        $this->addSql('ALTER TABLE contracts DROP FOREIGN KEY FK_950A973783E3463');
        $this->addSql('DROP TABLE contracts');
    }
}
