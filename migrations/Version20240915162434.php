<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240915162434 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE parent_entity (id INT AUTO_INCREMENT NOT NULL, father_last_name VARCHAR(255) NOT NULL, father_first_name VARCHAR(255) NOT NULL, father_email VARCHAR(255) NOT NULL, father_phone VARCHAR(20) NOT NULL, mother_last_name VARCHAR(255) NOT NULL, mother_first_name VARCHAR(255) NOT NULL, mother_email VARCHAR(255) NOT NULL, mother_phone VARCHAR(20) NOT NULL, family_status VARCHAR(20) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE room (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, max_students INT NOT NULL, comment LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE session (id INT AUTO_INCREMENT NOT NULL, room_id INT NOT NULL, study_class_id INT NOT NULL, teacher_id INT NOT NULL, start_time TIME NOT NULL, end_time TIME NOT NULL, INDEX IDX_D044D5D454177093 (room_id), INDEX IDX_D044D5D449891E99 (study_class_id), INDEX IDX_D044D5D441807E1D (teacher_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE student (id INT AUTO_INCREMENT NOT NULL, parent_id INT NOT NULL, last_name VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, birth_date DATE NOT NULL, previous_classes LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', gender VARCHAR(10) NOT NULL, address VARCHAR(255) NOT NULL, postal_code VARCHAR(10) NOT NULL, city VARCHAR(255) NOT NULL, level INT NOT NULL, UNIQUE INDEX UNIQ_B723AF33727ACA70 (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE study_class (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, level INT NOT NULL, speciality VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE teacher (id INT AUTO_INCREMENT NOT NULL, last_name VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, phone_number VARCHAR(20) NOT NULL, education_level VARCHAR(255) NOT NULL, specialities LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE session ADD CONSTRAINT FK_D044D5D454177093 FOREIGN KEY (room_id) REFERENCES room (id)');
        $this->addSql('ALTER TABLE session ADD CONSTRAINT FK_D044D5D449891E99 FOREIGN KEY (study_class_id) REFERENCES study_class (id)');
        $this->addSql('ALTER TABLE session ADD CONSTRAINT FK_D044D5D441807E1D FOREIGN KEY (teacher_id) REFERENCES teacher (id)');
        $this->addSql('ALTER TABLE student ADD CONSTRAINT FK_B723AF33727ACA70 FOREIGN KEY (parent_id) REFERENCES parent_entity (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE session DROP FOREIGN KEY FK_D044D5D454177093');
        $this->addSql('ALTER TABLE session DROP FOREIGN KEY FK_D044D5D449891E99');
        $this->addSql('ALTER TABLE session DROP FOREIGN KEY FK_D044D5D441807E1D');
        $this->addSql('ALTER TABLE student DROP FOREIGN KEY FK_B723AF33727ACA70');
        $this->addSql('DROP TABLE parent_entity');
        $this->addSql('DROP TABLE room');
        $this->addSql('DROP TABLE session');
        $this->addSql('DROP TABLE student');
        $this->addSql('DROP TABLE study_class');
        $this->addSql('DROP TABLE teacher');
    }
}
