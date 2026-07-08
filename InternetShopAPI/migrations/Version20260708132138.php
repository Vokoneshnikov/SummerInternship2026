<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260708132138 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Создание GIN индексов для триграммного поиска по товарам';
    }

    public function up(Schema $schema): void
    {
        // Вариант А: Отдельные индексы для каждого поля
        $this->addSql('CREATE INDEX idx_product_name_trgm ON product USING gin (name gin_trgm_ops)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IF EXISTS idx_product_name_trgm');
    }
}
