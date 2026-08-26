<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260825164130 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE import_file ADD user_id INT NOT NULL');
        $this->addSql('ALTER TABLE import_file ADD CONSTRAINT FK_61B3D890A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_61B3D890A76ED395 ON import_file (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE import_file DROP FOREIGN KEY FK_61B3D890A76ED395');
        $this->addSql('DROP INDEX IDX_61B3D890A76ED395 ON import_file');
        $this->addSql('ALTER TABLE import_file DROP user_id');
    }
}
