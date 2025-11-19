<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251119165000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable('contracts');
        $hasCreatedAt = $table->hasColumn('created_at');
        $hasUpdatedAt = $table->hasColumn('updated_at');
        $hasCourtId = $table->hasColumn('court_id');
        
        // Временно отключаем строгий режим для обработки невалидных дат
        $this->addSql("SET @old_sql_mode = @@sql_mode");
        $this->addSql("SET sql_mode = ''");
        
        // Обновляем невалидные значения дат, если колонки уже существуют
        if ($hasCreatedAt) {
            $this->addSql("UPDATE contracts SET created_at = NOW() WHERE created_at IS NULL OR created_at = '0000-00-00 00:00:00' OR created_at < '1970-01-01'");
        }
        if ($hasUpdatedAt) {
            $this->addSql("UPDATE contracts SET updated_at = NOW() WHERE updated_at IS NULL OR updated_at = '0000-00-00 00:00:00' OR updated_at < '1970-01-01'");
        }
        
        // Восстанавливаем строгий режим
        $this->addSql("SET sql_mode = @old_sql_mode");
        
        // Добавляем колонки, если их нет
        if (!$hasCreatedAt) {
            $this->addSql('ALTER TABLE contracts ADD created_at DATETIME DEFAULT NULL');
        } else {
            $this->addSql('ALTER TABLE contracts MODIFY created_at DATETIME DEFAULT NULL');
        }
        
        if (!$hasUpdatedAt) {
            $this->addSql('ALTER TABLE contracts ADD updated_at DATETIME DEFAULT NULL');
        } else {
            $this->addSql('ALTER TABLE contracts MODIFY updated_at DATETIME DEFAULT NULL');
        }
        
        if (!$hasCourtId) {
            $this->addSql('ALTER TABLE contracts ADD court_id INT DEFAULT NULL');
        }
        
        // Обновляем все NULL значения для существующих записей
        $this->addSql('UPDATE contracts SET created_at = NOW() WHERE created_at IS NULL');
        $this->addSql('UPDATE contracts SET updated_at = NOW() WHERE updated_at IS NULL');
        
        // Делаем колонки NOT NULL
        $this->addSql('ALTER TABLE contracts MODIFY created_at DATETIME NOT NULL, MODIFY updated_at DATETIME NOT NULL');
        
        // Добавляем внешний ключ и индекс для court_id, если их еще нет
        if (!$table->hasForeignKey('FK_950A973E3184009')) {
            $this->addSql('ALTER TABLE contracts ADD CONSTRAINT FK_950A973E3184009 FOREIGN KEY (court_id) REFERENCES courts (id) ON DELETE SET NULL');
        }
        if (!$table->hasIndex('UNIQ_950A973E3184009')) {
            $this->addSql('CREATE UNIQUE INDEX UNIQ_950A973E3184009 ON contracts (court_id)');
        }
        
        $this->addSql('ALTER TABLE creditors CHANGE bank_details bank_details LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE fns CHANGE bank_details bank_details LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contracts DROP FOREIGN KEY FK_950A973E3184009');
        $this->addSql('DROP INDEX UNIQ_950A973E3184009 ON contracts');
        $this->addSql('ALTER TABLE contracts DROP created_at, DROP updated_at, DROP court_id');
        $this->addSql('ALTER TABLE creditors CHANGE bank_details bank_details TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE fns CHANGE bank_details bank_details TEXT DEFAULT NULL');
    }
}
