<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250916154830 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE demande (id INT AUTO_INCREMENT NOT NULL, parent_id INT NOT NULL, student_id INT DEFAULT NULL, target_study_class_id INT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', type VARCHAR(50) NOT NULL, status VARCHAR(20) NOT NULL, comment LONGTEXT DEFAULT NULL, INDEX IDX_2694D7A5727ACA70 (parent_id), INDEX IDX_2694D7A5CB944F1A (student_id), INDEX IDX_2694D7A5F5F311B0 (target_study_class_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE demande ADD CONSTRAINT FK_2694D7A5727ACA70 FOREIGN KEY (parent_id) REFERENCES parent_entity (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE demande ADD CONSTRAINT FK_2694D7A5CB944F1A FOREIGN KEY (student_id) REFERENCES student (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE demande ADD CONSTRAINT FK_2694D7A5F5F311B0 FOREIGN KEY (target_study_class_id) REFERENCES study_class (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE parent_entity ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE parent_entity ADD CONSTRAINT FK_413B87AEA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_413B87AEA76ED395 ON parent_entity (user_id)');
        $this->addSql('ALTER TABLE registration_arabic_cours ADD was_enrolled2024 VARCHAR(255) DEFAULT NULL, ADD previous_level VARCHAR(255) DEFAULT NULL, ADD sibling_enrolled VARCHAR(255) DEFAULT NULL, CHANGE authorized authorized JSON NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE roles roles JSON NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE demande DROP FOREIGN KEY FK_2694D7A5727ACA70');
        $this->addSql('ALTER TABLE demande DROP FOREIGN KEY FK_2694D7A5CB944F1A');
        $this->addSql('ALTER TABLE demande DROP FOREIGN KEY FK_2694D7A5F5F311B0');
        $this->addSql('DROP TABLE demande');
        $this->addSql('ALTER TABLE parent_entity DROP FOREIGN KEY FK_413B87AEA76ED395');
        $this->addSql('DROP INDEX UNIQ_413B87AEA76ED395 ON parent_entity');
        $this->addSql('ALTER TABLE parent_entity DROP user_id');
        $this->addSql('ALTER TABLE registration_arabic_cours DROP was_enrolled2024, DROP previous_level, DROP sibling_enrolled, CHANGE authorized authorized LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE roles roles LONGTEXT NOT NULL');
    }
}
