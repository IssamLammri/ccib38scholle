<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250425214032 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE invoice (id INT AUTO_INCREMENT NOT NULL, parent_id INT NOT NULL, invoice_date DATE NOT NULL, total_amount NUMERIC(10, 2) NOT NULL, comment VARCHAR(255) DEFAULT NULL, discount NUMERIC(10, 2) DEFAULT NULL, INDEX IDX_90651744727ACA70 (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE parent_entity (id INT AUTO_INCREMENT NOT NULL, father_last_name VARCHAR(255) NOT NULL, father_first_name VARCHAR(255) NOT NULL, father_email VARCHAR(255) NOT NULL, father_phone VARCHAR(20) NOT NULL, mother_last_name VARCHAR(255) NOT NULL, mother_first_name VARCHAR(255) NOT NULL, mother_email VARCHAR(255) NOT NULL, mother_phone VARCHAR(20) NOT NULL, family_status VARCHAR(20) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE payment (id INT AUTO_INCREMENT NOT NULL, parent_id INT NOT NULL, student_id INT DEFAULT NULL, study_class_id INT DEFAULT NULL, invoice_id INT DEFAULT NULL, amount_paid NUMERIC(10, 2) NOT NULL, service_type VARCHAR(255) NOT NULL, payment_date DATE NOT NULL, payment_type VARCHAR(255) NOT NULL, month VARCHAR(255) DEFAULT NULL, year INT DEFAULT NULL, comment TINYTEXT DEFAULT NULL, INDEX IDX_6D28840D727ACA70 (parent_id), INDEX IDX_6D28840DCB944F1A (student_id), INDEX IDX_6D28840D49891E99 (study_class_id), INDEX IDX_6D28840D2989F1FD (invoice_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE registration_arabic_cours (id INT AUTO_INCREMENT NOT NULL, token VARCHAR(32) NOT NULL, child_first_name VARCHAR(100) NOT NULL, child_last_name VARCHAR(100) NOT NULL, child_dob DATE NOT NULL, child_gender VARCHAR(10) NOT NULL, child_level VARCHAR(50) NOT NULL, father_first_name VARCHAR(100) NOT NULL, father_last_name VARCHAR(100) NOT NULL, mother_first_name VARCHAR(100) NOT NULL, mother_last_name VARCHAR(100) NOT NULL, contact_email VARCHAR(180) NOT NULL, father_phone VARCHAR(20) NOT NULL, mother_phone VARCHAR(20) NOT NULL, address VARCHAR(255) NOT NULL, postal_code VARCHAR(10) NOT NULL, city VARCHAR(100) NOT NULL, authorized JSON NOT NULL, authorized_other_name VARCHAR(100) DEFAULT NULL, authorized_other_relation VARCHAR(50) DEFAULT NULL, leave_alone VARCHAR(3) NOT NULL, image_rights VARCHAR(3) NOT NULL, commitment_situ TINYINT(1) NOT NULL, medical_info VARCHAR(3) NOT NULL, medical_details LONGTEXT DEFAULT NULL, medical_treatment VARCHAR(3) NOT NULL, legal_declaration TINYINT(1) NOT NULL, payment_terms TINYINT(1) NOT NULL, child_photo_filename VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_37E83FD95F37A13B (token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE room (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, max_students INT NOT NULL, comment LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE session (id INT AUTO_INCREMENT NOT NULL, room_id INT NOT NULL, study_class_id INT NOT NULL, teacher_id INT NOT NULL, start_time DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', end_time DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_D044D5D454177093 (room_id), INDEX IDX_D044D5D449891E99 (study_class_id), INDEX IDX_D044D5D441807E1D (teacher_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE session_study_class_presence (id INT AUTO_INCREMENT NOT NULL, student_id INT DEFAULT NULL, session_id INT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', is_present TINYINT(1) DEFAULT NULL, INDEX IDX_2E87B39CCB944F1A (student_id), INDEX IDX_2E87B39C613FECDF (session_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE student (id INT AUTO_INCREMENT NOT NULL, parent_id INT NOT NULL, last_name VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, birth_date DATE NOT NULL, previous_classes LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', gender VARCHAR(10) NOT NULL, address VARCHAR(255) NOT NULL, postal_code VARCHAR(10) NOT NULL, city VARCHAR(255) NOT NULL, level INT NOT NULL, INDEX IDX_B723AF33727ACA70 (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE student_class_registered (id INT AUTO_INCREMENT NOT NULL, student_id INT NOT NULL, study_class_id INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', active TINYINT(1) DEFAULT NULL, INDEX IDX_2FA232B7CB944F1A (student_id), INDEX IDX_2FA232B749891E99 (study_class_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE study_class (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, level INT NOT NULL, speciality VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE teacher (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, last_name VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, phone_number VARCHAR(20) NOT NULL, education_level VARCHAR(255) NOT NULL, specialities LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', UNIQUE INDEX UNIQ_B0F6A6D5A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, first_name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) DEFAULT NULL, agree_terms TINYINT(1) DEFAULT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, is_verified TINYINT(1) NOT NULL, verification_token VARCHAR(255) DEFAULT NULL, token_expiration DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_90651744727ACA70 FOREIGN KEY (parent_id) REFERENCES parent_entity (id)');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D727ACA70 FOREIGN KEY (parent_id) REFERENCES parent_entity (id)');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840DCB944F1A FOREIGN KEY (student_id) REFERENCES student (id)');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D49891E99 FOREIGN KEY (study_class_id) REFERENCES study_class (id)');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D2989F1FD FOREIGN KEY (invoice_id) REFERENCES invoice (id)');
        $this->addSql('ALTER TABLE session ADD CONSTRAINT FK_D044D5D454177093 FOREIGN KEY (room_id) REFERENCES room (id)');
        $this->addSql('ALTER TABLE session ADD CONSTRAINT FK_D044D5D449891E99 FOREIGN KEY (study_class_id) REFERENCES study_class (id)');
        $this->addSql('ALTER TABLE session ADD CONSTRAINT FK_D044D5D441807E1D FOREIGN KEY (teacher_id) REFERENCES teacher (id)');
        $this->addSql('ALTER TABLE session_study_class_presence ADD CONSTRAINT FK_2E87B39CCB944F1A FOREIGN KEY (student_id) REFERENCES student (id)');
        $this->addSql('ALTER TABLE session_study_class_presence ADD CONSTRAINT FK_2E87B39C613FECDF FOREIGN KEY (session_id) REFERENCES session (id)');
        $this->addSql('ALTER TABLE student ADD CONSTRAINT FK_B723AF33727ACA70 FOREIGN KEY (parent_id) REFERENCES parent_entity (id)');
        $this->addSql('ALTER TABLE student_class_registered ADD CONSTRAINT FK_2FA232B7CB944F1A FOREIGN KEY (student_id) REFERENCES student (id)');
        $this->addSql('ALTER TABLE student_class_registered ADD CONSTRAINT FK_2FA232B749891E99 FOREIGN KEY (study_class_id) REFERENCES study_class (id)');
        $this->addSql('ALTER TABLE teacher ADD CONSTRAINT FK_B0F6A6D5A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE invoice DROP FOREIGN KEY FK_90651744727ACA70');
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840D727ACA70');
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840DCB944F1A');
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840D49891E99');
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840D2989F1FD');
        $this->addSql('ALTER TABLE session DROP FOREIGN KEY FK_D044D5D454177093');
        $this->addSql('ALTER TABLE session DROP FOREIGN KEY FK_D044D5D449891E99');
        $this->addSql('ALTER TABLE session DROP FOREIGN KEY FK_D044D5D441807E1D');
        $this->addSql('ALTER TABLE session_study_class_presence DROP FOREIGN KEY FK_2E87B39CCB944F1A');
        $this->addSql('ALTER TABLE session_study_class_presence DROP FOREIGN KEY FK_2E87B39C613FECDF');
        $this->addSql('ALTER TABLE student DROP FOREIGN KEY FK_B723AF33727ACA70');
        $this->addSql('ALTER TABLE student_class_registered DROP FOREIGN KEY FK_2FA232B7CB944F1A');
        $this->addSql('ALTER TABLE student_class_registered DROP FOREIGN KEY FK_2FA232B749891E99');
        $this->addSql('ALTER TABLE teacher DROP FOREIGN KEY FK_B0F6A6D5A76ED395');
        $this->addSql('DROP TABLE invoice');
        $this->addSql('DROP TABLE parent_entity');
        $this->addSql('DROP TABLE payment');
        $this->addSql('DROP TABLE registration_arabic_cours');
        $this->addSql('DROP TABLE room');
        $this->addSql('DROP TABLE session');
        $this->addSql('DROP TABLE session_study_class_presence');
        $this->addSql('DROP TABLE student');
        $this->addSql('DROP TABLE student_class_registered');
        $this->addSql('DROP TABLE study_class');
        $this->addSql('DROP TABLE teacher');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
