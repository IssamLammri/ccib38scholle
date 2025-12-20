<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251022133126 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE student_class_registered DROP FOREIGN KEY FK_2FA232B7CB944F1A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE student_class_registered ADD CONSTRAINT FK_2FA232B7CB944F1A FOREIGN KEY (student_id) REFERENCES student (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE study_class DROP FOREIGN KEY FK_B231497FBF78C012
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE study_class ADD CONSTRAINT FK_B231497FBF78C012 FOREIGN KEY (principal_room_id) REFERENCES room (id) ON DELETE SET NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE student_class_registered DROP FOREIGN KEY FK_2FA232B7CB944F1A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE student_class_registered ADD CONSTRAINT FK_2FA232B7CB944F1A FOREIGN KEY (student_id) REFERENCES student (id) ON UPDATE NO ACTION ON DELETE NO ACTION
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE study_class DROP FOREIGN KEY FK_B231497FBF78C012
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE study_class ADD CONSTRAINT FK_B231497FBF78C012 FOREIGN KEY (principal_room_id) REFERENCES room (id) ON UPDATE NO ACTION ON DELETE NO ACTION
        SQL);
    }
}
