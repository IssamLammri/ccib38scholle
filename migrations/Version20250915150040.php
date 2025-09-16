<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250915150040 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE parent_entity ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE parent_entity ADD CONSTRAINT FK_413B87AEA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_413B87AEA76ED395 ON parent_entity (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE parent_entity DROP FOREIGN KEY FK_413B87AEA76ED395');
        $this->addSql('DROP INDEX UNIQ_413B87AEA76ED395 ON parent_entity');
        $this->addSql('ALTER TABLE parent_entity DROP user_id');
    }
}
