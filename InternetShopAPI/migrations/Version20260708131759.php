<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260708131759 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Активация расширения pg_trgm для поиска по триграммам';
    }

    public function up(Schema $schema): void
    {
        // Используем IF NOT EXISTS для безопасности
        $this->addSql('CREATE EXTENSION IF NOT EXISTS pg_trgm');
    }

    public function down(Schema $schema): void
    {
        // ВАЖНО: Удаление расширения может нарушить работу существующих индексов
        // Будьте осторожны с этим в продакшене
        $this->addSql('DROP EXTENSION IF EXISTS pg_trgm');
    }
}
