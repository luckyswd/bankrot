<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Удаление уникального индекса с court_id для поддержки связи ManyToOne
 */
final class Version20250124000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Удаление уникального индекса UNIQ_950A973E3184009 с court_id в таблице contracts';
    }

    public function up(Schema $schema): void
    {
        $connection = $this->connection;
        $tableName = 'contracts';

        // Проверяем существование внешнего ключа через INFORMATION_SCHEMA
        $fkExists = $connection->fetchOne(
            "SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
             WHERE TABLE_SCHEMA = DATABASE() 
             AND TABLE_NAME = ? 
             AND CONSTRAINT_NAME = 'FK_950A973E3184009'",
            [$tableName]
        ) > 0;

        // Сначала удаляем внешний ключ, так как он использует уникальный индекс
        if ($fkExists) {
            $this->addSql('ALTER TABLE contracts DROP FOREIGN KEY FK_950A973E3184009');
        }

        // Проверяем существование уникального индекса
        $uniqueIndexExists = $connection->fetchOne(
            "SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
             WHERE TABLE_SCHEMA = DATABASE() 
             AND TABLE_NAME = ? 
             AND INDEX_NAME = 'UNIQ_950A973E3184009'",
            [$tableName]
        ) > 0;

        // Удаляем уникальный индекс
        if ($uniqueIndexExists) {
            $this->addSql('DROP INDEX UNIQ_950A973E3184009 ON contracts');
        }

        // Проверяем существование обычного индекса
        $indexExists = $connection->fetchOne(
            "SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
             WHERE TABLE_SCHEMA = DATABASE() 
             AND TABLE_NAME = ? 
             AND INDEX_NAME = 'IDX_950A973E3184009'",
            [$tableName]
        ) > 0;

        // Создаем обычный индекс для оптимизации запросов
        if (!$indexExists) {
            $this->addSql('CREATE INDEX IDX_950A973E3184009 ON contracts (court_id)');
        }

        // Восстанавливаем внешний ключ (всегда, так как мы его удалили выше если он существовал)
        $this->addSql('ALTER TABLE contracts ADD CONSTRAINT FK_950A973E3184009 FOREIGN KEY (court_id) REFERENCES courts (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $connection = $this->connection;
        $tableName = 'contracts';

        // Проверяем существование внешнего ключа
        $fkExists = $connection->fetchOne(
            "SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
             WHERE TABLE_SCHEMA = DATABASE() 
             AND TABLE_NAME = ? 
             AND CONSTRAINT_NAME = 'FK_950A973E3184009'",
            [$tableName]
        ) > 0;

        // Удаляем внешний ключ
        if ($fkExists) {
            $this->addSql('ALTER TABLE contracts DROP FOREIGN KEY FK_950A973E3184009');
        }

        // Проверяем существование обычного индекса
        $indexExists = $connection->fetchOne(
            "SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
             WHERE TABLE_SCHEMA = DATABASE() 
             AND TABLE_NAME = ? 
             AND INDEX_NAME = 'IDX_950A973E3184009'",
            [$tableName]
        ) > 0;

        // Удаляем обычный индекс
        if ($indexExists) {
            $this->addSql('DROP INDEX IDX_950A973E3184009 ON contracts');
        }

        // Проверяем существование уникального индекса
        $uniqueIndexExists = $connection->fetchOne(
            "SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
             WHERE TABLE_SCHEMA = DATABASE() 
             AND TABLE_NAME = ? 
             AND INDEX_NAME = 'UNIQ_950A973E3184009'",
            [$tableName]
        ) > 0;

        // Восстанавливаем уникальный индекс
        if (!$uniqueIndexExists) {
            $this->addSql('CREATE UNIQUE INDEX UNIQ_950A973E3184009 ON contracts (court_id)');
        }

        // Восстанавливаем внешний ключ
        $this->addSql('ALTER TABLE contracts ADD CONSTRAINT FK_950A973E3184009 FOREIGN KEY (court_id) REFERENCES courts (id) ON DELETE SET NULL');
    }
}

