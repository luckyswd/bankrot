<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260905090000 extends AbstractMigration
{
    private const TABLE = 'contracts';
    private const UNIQUE_COURT_INDEX = 'UNIQ_950A973E3184009';
    private const COURT_INDEX = 'IDX_950A973E3184009';
    private const MANAGER_FOREIGN_KEY = 'FK_950A973783E3463';
    private const MANAGER_INDEX = 'IDX_950A973783E3463';
    private const OBSOLETE_COLUMNS = [
        'manager_id',
        'sex',
        'judicial_realization_decision_date',
        'judicial_realization_resolution_date',
    ];

    public function getDescription(): string
    {
        return 'Приведение таблицы contracts к текущим сущностям: обычный индекс по court_id вместо уникального, удаление колонок manager_id, sex и дат реализации';
    }

    public function up(Schema $schema): void
    {
        $this->skipIf(!$this->tableExists(self::TABLE), 'Таблица contracts ещё не создана');

        if ($this->indexExists(self::UNIQUE_COURT_INDEX)) {
            if ($this->foreignKeyExists('FK_950A973E3184009')) {
                $this->addSql('ALTER TABLE contracts DROP FOREIGN KEY FK_950A973E3184009');
            }

            $this->addSql('DROP INDEX ' . self::UNIQUE_COURT_INDEX . ' ON contracts');

            if (!$this->indexExists(self::COURT_INDEX)) {
                $this->addSql('CREATE INDEX ' . self::COURT_INDEX . ' ON contracts (court_id)');
            }

            $this->addSql('ALTER TABLE contracts ADD CONSTRAINT FK_950A973E3184009 FOREIGN KEY (court_id) REFERENCES courts (id) ON DELETE SET NULL');
        }

        if ($this->foreignKeyExists(self::MANAGER_FOREIGN_KEY)) {
            $this->addSql('ALTER TABLE contracts DROP FOREIGN KEY ' . self::MANAGER_FOREIGN_KEY);
        }

        if ($this->indexExists(self::MANAGER_INDEX)) {
            $this->addSql('DROP INDEX ' . self::MANAGER_INDEX . ' ON contracts');
        }

        foreach (self::OBSOLETE_COLUMNS as $column) {
            if ($this->columnExists($column)) {
                $this->addSql(sprintf('ALTER TABLE contracts DROP %s', $column));
            }
        }
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('Удалённые колонки contracts восстановлению не подлежат');
    }

    private function tableExists(string $table): bool
    {
        return (int)$this->connection->fetchOne(
            'SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?',
            [$table]
        ) > 0;
    }

    private function columnExists(string $column): bool
    {
        return (int)$this->connection->fetchOne(
            'SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            [self::TABLE, $column]
        ) > 0;
    }

    private function indexExists(string $index): bool
    {
        return (int)$this->connection->fetchOne(
            'SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?',
            [self::TABLE, $index]
        ) > 0;
    }

    private function foreignKeyExists(string $constraint): bool
    {
        return (int)$this->connection->fetchOne(
            'SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND CONSTRAINT_NAME = ? AND CONSTRAINT_TYPE = ?',
            [self::TABLE, $constraint, 'FOREIGN KEY']
        ) > 0;
    }
}
