<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251022131736 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE session_study_class_presence DROP FOREIGN KEY FK_2E87B39CCB944F1A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE session_study_class_presence CHANGE student_id student_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE session_study_class_presence ADD CONSTRAINT FK_2E87B39CCB944F1A FOREIGN KEY (student_id) REFERENCES student (id) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE session_study_class_presence DROP FOREIGN KEY FK_2E87B39CCB944F1A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE session_study_class_presence CHANGE student_id student_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE session_study_class_presence ADD CONSTRAINT FK_2E87B39CCB944F1A FOREIGN KEY (student_id) REFERENCES student (id) ON UPDATE NO ACTION ON DELETE NO ACTION
        SQL);
    }
}
