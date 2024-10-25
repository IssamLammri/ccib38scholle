<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241022145729 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE session_study_class_presence ADD session_id INT DEFAULT NULL, CHANGE student_id student_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE session_study_class_presence ADD CONSTRAINT FK_2E87B39C613FECDF FOREIGN KEY (session_id) REFERENCES session (id)');
        $this->addSql('CREATE INDEX IDX_2E87B39C613FECDF ON session_study_class_presence (session_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE session_study_class_presence DROP FOREIGN KEY FK_2E87B39C613FECDF');
        $this->addSql('DROP INDEX IDX_2E87B39C613FECDF ON session_study_class_presence');
        $this->addSql('ALTER TABLE session_study_class_presence DROP session_id, CHANGE student_id student_id INT NOT NULL');
    }
}
