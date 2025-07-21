<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250720182729 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE global_message (id SERIAL NOT NULL, sender VARCHAR(255) NOT NULL, sender_id INT DEFAULT NULL, text VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE room (id SERIAL NOT NULL, owner INT NOT NULL, name VARCHAR(255) NOT NULL, game VARCHAR(255) NOT NULL, max_player INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE room_participant (id SERIAL NOT NULL, room_id INT NOT NULL, user_id INT NOT NULL, username VARCHAR(255) NOT NULL, avatar VARCHAR(255) NOT NULL, owner BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C3AAC52E54177093 ON room_participant (room_id)');
        $this->addSql('CREATE INDEX IDX_C3AAC52EA76ED395 ON room_participant (user_id)');
        $this->addSql('CREATE TABLE "user" (id SERIAL NOT NULL, username VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, avatar VARCHAR(255) NOT NULL, game_played INT DEFAULT NULL, game_won INT DEFAULT NULL, roles JSON NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE room_participant ADD CONSTRAINT FK_C3AAC52E54177093 FOREIGN KEY (room_id) REFERENCES room (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE room_participant ADD CONSTRAINT FK_C3AAC52EA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE room_participant DROP CONSTRAINT FK_C3AAC52E54177093');
        $this->addSql('ALTER TABLE room_participant DROP CONSTRAINT FK_C3AAC52EA76ED395');
        $this->addSql('DROP TABLE global_message');
        $this->addSql('DROP TABLE room');
        $this->addSql('DROP TABLE room_participant');
        $this->addSql('DROP TABLE "user"');
    }
}
