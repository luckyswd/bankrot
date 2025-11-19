<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251119173117 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE contracts_creditors (contracts_id INT NOT NULL, creditor_id INT NOT NULL, INDEX IDX_18D3991024584564 (contracts_id), INDEX IDX_18D39910DF91AC92 (creditor_id), PRIMARY KEY (contracts_id, creditor_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE contracts_creditors ADD CONSTRAINT FK_18D3991024584564 FOREIGN KEY (contracts_id) REFERENCES contracts (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contracts_creditors ADD CONSTRAINT FK_18D39910DF91AC92 FOREIGN KEY (creditor_id) REFERENCES creditors (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contracts ADD case_number VARCHAR(255) DEFAULT NULL, ADD hearing_date_time DATETIME DEFAULT NULL, ADD efrsb_date_time DATETIME DEFAULT NULL, ADD efrsb_cabinet VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contracts_creditors DROP FOREIGN KEY FK_18D3991024584564');
        $this->addSql('ALTER TABLE contracts_creditors DROP FOREIGN KEY FK_18D39910DF91AC92');
        $this->addSql('DROP TABLE contracts_creditors');
        $this->addSql('ALTER TABLE contracts DROP case_number, DROP hearing_date_time, DROP efrsb_date_time, DROP efrsb_cabinet');
    }
}
