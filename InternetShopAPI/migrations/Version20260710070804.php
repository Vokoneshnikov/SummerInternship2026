<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260710123456 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Install pg_trgm extension for similarity search';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE EXTENSION IF NOT EXISTS pg_trgm;');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP EXTENSION pg_trgm;');
    }
}
