<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260707085323 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles CLOB NOT NULL, password VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON user (email)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__links AS SELECT id, old_link, new_link, created_at, last_used_at, usage_count FROM links');
        $this->addSql('DROP TABLE links');
        $this->addSql('CREATE TABLE links (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, old_link VARCHAR(255) NOT NULL, new_link VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, last_used_at DATETIME NOT NULL, usage_count INTEGER NOT NULL, is_disposable BOOLEAN NOT NULL, expires_at DATETIME NOT NULL, user_id INTEGER NOT NULL, CONSTRAINT FK_D182A118A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO links (id, old_link, new_link, created_at, last_used_at, usage_count) SELECT id, old_link, new_link, created_at, last_used_at, usage_count FROM __temp__links');
        $this->addSql('DROP TABLE __temp__links');
        $this->addSql('CREATE INDEX IDX_D182A118A76ED395 ON links (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE user');
        $this->addSql('CREATE TEMPORARY TABLE __temp__links AS SELECT id, old_link, new_link, created_at, last_used_at, usage_count FROM links');
        $this->addSql('DROP TABLE links');
        $this->addSql('CREATE TABLE links (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, old_link VARCHAR(255) NOT NULL, new_link VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, last_used_at DATETIME NOT NULL, usage_count INTEGER NOT NULL)');
        $this->addSql('INSERT INTO links (id, old_link, new_link, created_at, last_used_at, usage_count) SELECT id, old_link, new_link, created_at, last_used_at, usage_count FROM __temp__links');
        $this->addSql('DROP TABLE __temp__links');
    }
}
