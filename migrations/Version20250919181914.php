<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250919181914 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE study_class ADD principal_room_id INT DEFAULT NULL, ADD school_year VARCHAR(9) NOT NULL');
        $this->addSql('ALTER TABLE study_class ADD CONSTRAINT FK_B231497FBF78C012 FOREIGN KEY (principal_room_id) REFERENCES room (id)');
        $this->addSql('CREATE INDEX IDX_B231497FBF78C012 ON study_class (principal_room_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE study_class DROP FOREIGN KEY FK_B231497FBF78C012');
        $this->addSql('DROP INDEX IDX_B231497FBF78C012 ON study_class');
        $this->addSql('ALTER TABLE study_class DROP principal_room_id, DROP school_year');
    }
}
