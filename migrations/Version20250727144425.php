<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250727144425 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE study_class ADD principal_teacher_id INT DEFAULT NULL, ADD day VARCHAR(50) NOT NULL, ADD start_hour TIME NOT NULL, ADD end_hour TIME NOT NULL, ADD class_type VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE study_class ADD CONSTRAINT FK_B231497F98BFDD30 FOREIGN KEY (principal_teacher_id) REFERENCES teacher (id)');
        $this->addSql('CREATE INDEX IDX_B231497F98BFDD30 ON study_class (principal_teacher_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE study_class DROP FOREIGN KEY FK_B231497F98BFDD30');
        $this->addSql('DROP INDEX IDX_B231497F98BFDD30 ON study_class');
        $this->addSql('ALTER TABLE study_class DROP principal_teacher_id, DROP day, DROP start_hour, DROP end_hour, DROP class_type');
    }
}
