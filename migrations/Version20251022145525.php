<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251022145525 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE academic_support_registration (id INT AUTO_INCREMENT NOT NULL, public_token VARCHAR(64) NOT NULL, student_first_name VARCHAR(100) NOT NULL, student_last_name VARCHAR(100) NOT NULL, level VARCHAR(50) NOT NULL, subjects JSON NOT NULL, parent_first_name VARCHAR(100) NOT NULL, parent_last_name VARCHAR(100) NOT NULL, email VARCHAR(150) NOT NULL, phone VARCHAR(30) DEFAULT NULL, mother_first_name VARCHAR(100) DEFAULT NULL, mother_last_name VARCHAR(100) DEFAULT NULL, mother_phone VARCHAR(30) DEFAULT NULL, accepted_payment_terms TINYINT(1) NOT NULL, status VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', address VARCHAR(255) DEFAULT NULL, postal_code VARCHAR(12) DEFAULT NULL, city VARCHAR(150) DEFAULT NULL, updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', student_birth_date DATE DEFAULT NULL COMMENT '(DC2Type:date_immutable)', UNIQUE INDEX UNIQ_75CA9808AE981E3B (public_token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE book (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(180) NOT NULL, description LONGTEXT DEFAULT NULL, image_name VARCHAR(255) DEFAULT NULL, sale_price NUMERIC(10, 2) NOT NULL, purchase_price NUMERIC(10, 2) NOT NULL, level VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE contact (id INT AUTO_INCREMENT NOT NULL, public_token VARCHAR(64) NOT NULL, email VARCHAR(150) NOT NULL, first_name VARCHAR(100) DEFAULT NULL, last_name VARCHAR(100) DEFAULT NULL, phone VARCHAR(30) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, postal_code VARCHAR(12) DEFAULT NULL, city VARCHAR(150) DEFAULT NULL, locale VARCHAR(8) DEFAULT 'fr' NOT NULL, tags JSON NOT NULL, segments JSON NOT NULL, consent_newsletter TINYINT(1) DEFAULT 0 NOT NULL, consent_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', consent_ip VARCHAR(45) DEFAULT NULL, consent_source VARCHAR(100) DEFAULT NULL, status VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', unsubscribed_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', last_emailed_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', UNIQUE INDEX UNIQ_4C62E638AE981E3B (public_token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE demande (id INT AUTO_INCREMENT NOT NULL, parent_id INT NOT NULL, student_id INT DEFAULT NULL, target_study_class_id INT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', type VARCHAR(50) NOT NULL, status VARCHAR(20) NOT NULL, comment LONGTEXT DEFAULT NULL, INDEX IDX_2694D7A5727ACA70 (parent_id), INDEX IDX_2694D7A5CB944F1A (student_id), INDEX IDX_2694D7A5F5F311B0 (target_study_class_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE invoice (id INT AUTO_INCREMENT NOT NULL, parent_id INT NOT NULL, invoice_date DATE NOT NULL, total_amount NUMERIC(10, 2) NOT NULL, comment VARCHAR(255) DEFAULT NULL, discount NUMERIC(10, 2) DEFAULT NULL, INDEX IDX_90651744727ACA70 (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE parent_entity (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, father_last_name VARCHAR(255) NOT NULL, father_first_name VARCHAR(255) NOT NULL, father_email VARCHAR(255) NOT NULL, father_phone VARCHAR(20) NOT NULL, mother_last_name VARCHAR(255) NOT NULL, mother_first_name VARCHAR(255) NOT NULL, mother_email VARCHAR(255) NOT NULL, mother_phone VARCHAR(20) NOT NULL, family_status VARCHAR(20) NOT NULL, UNIQUE INDEX UNIQ_413B87AEA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE payment (id INT AUTO_INCREMENT NOT NULL, parent_id INT NOT NULL, student_id INT DEFAULT NULL, study_class_id INT DEFAULT NULL, invoice_id INT DEFAULT NULL, processed_by_id INT DEFAULT NULL, amount_paid NUMERIC(10, 2) NOT NULL, service_type VARCHAR(255) NOT NULL, payment_date DATE NOT NULL, payment_type VARCHAR(255) NOT NULL, month VARCHAR(255) DEFAULT NULL, year INT DEFAULT NULL, comment TINYTEXT DEFAULT NULL, INDEX IDX_6D28840D727ACA70 (parent_id), INDEX IDX_6D28840DCB944F1A (student_id), INDEX IDX_6D28840D49891E99 (study_class_id), INDEX IDX_6D28840D2989F1FD (invoice_id), INDEX IDX_6D28840D2FFD4FD3 (processed_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE payment_book_item (id INT AUTO_INCREMENT NOT NULL, payment_id INT NOT NULL, book_id INT NOT NULL, quantity INT NOT NULL, unit_price NUMERIC(10, 2) NOT NULL, line_total NUMERIC(10, 2) NOT NULL, INDEX IDX_45AF413E4C3A3BB (payment_id), INDEX IDX_45AF413E16A2B381 (book_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE registration_arabic_cours (id INT AUTO_INCREMENT NOT NULL, student_id INT DEFAULT NULL, token VARCHAR(32) NOT NULL, child_first_name VARCHAR(100) NOT NULL, child_last_name VARCHAR(100) NOT NULL, child_dob DATE NOT NULL, child_gender VARCHAR(10) NOT NULL, child_level VARCHAR(50) NOT NULL, father_first_name VARCHAR(100) NOT NULL, father_last_name VARCHAR(100) NOT NULL, mother_first_name VARCHAR(100) NOT NULL, mother_last_name VARCHAR(100) NOT NULL, contact_email VARCHAR(180) NOT NULL, father_phone VARCHAR(20) NOT NULL, mother_phone VARCHAR(20) NOT NULL, address VARCHAR(255) NOT NULL, postal_code VARCHAR(10) NOT NULL, city VARCHAR(100) NOT NULL, authorized JSON NOT NULL, authorized_other_name VARCHAR(100) DEFAULT NULL, authorized_other_relation VARCHAR(50) DEFAULT NULL, leave_alone VARCHAR(3) NOT NULL, image_rights VARCHAR(3) NOT NULL, commitment_situ TINYINT(1) NOT NULL, medical_info VARCHAR(3) NOT NULL, medical_details LONGTEXT DEFAULT NULL, medical_treatment VARCHAR(3) NOT NULL, step_registration VARCHAR(100) NOT NULL, legal_declaration TINYINT(1) NOT NULL, payment_terms TINYINT(1) NOT NULL, child_photo_filename VARCHAR(255) DEFAULT NULL, previous_level VARCHAR(255) DEFAULT NULL, sibling_enrolled VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', UNIQUE INDEX UNIQ_37E83FD95F37A13B (token), INDEX IDX_37E83FD9CB944F1A (student_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE room (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, max_students INT NOT NULL, comment LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE session (id INT AUTO_INCREMENT NOT NULL, room_id INT NOT NULL, study_class_id INT NOT NULL, teacher_id INT NOT NULL, start_time DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', end_time DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_D044D5D454177093 (room_id), INDEX IDX_D044D5D449891E99 (study_class_id), INDEX IDX_D044D5D441807E1D (teacher_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE session_study_class_presence (id INT AUTO_INCREMENT NOT NULL, student_id INT DEFAULT NULL, session_id INT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', is_present TINYINT(1) DEFAULT NULL, INDEX IDX_2E87B39CCB944F1A (student_id), INDEX IDX_2E87B39C613FECDF (session_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE student (id INT AUTO_INCREMENT NOT NULL, parent_id INT NOT NULL, last_name VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, birth_date DATE NOT NULL, previous_classes LONGTEXT NOT NULL COMMENT '(DC2Type:array)', gender VARCHAR(10) NOT NULL, address VARCHAR(255) NOT NULL, postal_code VARCHAR(10) NOT NULL, city VARCHAR(255) NOT NULL, level VARCHAR(255) NOT NULL, INDEX IDX_B723AF33727ACA70 (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE student_class_registered (id INT AUTO_INCREMENT NOT NULL, student_id INT NOT NULL, study_class_id INT NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', active TINYINT(1) DEFAULT NULL, INDEX IDX_2FA232B7CB944F1A (student_id), INDEX IDX_2FA232B749891E99 (study_class_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE study_class (id INT AUTO_INCREMENT NOT NULL, principal_teacher_id INT DEFAULT NULL, principal_room_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, level VARCHAR(255) NOT NULL, speciality VARCHAR(255) NOT NULL, day VARCHAR(50) NOT NULL, start_hour TIME NOT NULL, end_hour TIME NOT NULL, class_type VARCHAR(100) NOT NULL, school_year VARCHAR(9) NOT NULL, INDEX IDX_B231497F98BFDD30 (principal_teacher_id), INDEX IDX_B231497FBF78C012 (principal_room_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE teacher (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, last_name VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, phone_number VARCHAR(20) NOT NULL, education_level VARCHAR(255) NOT NULL, specialities LONGTEXT NOT NULL COMMENT '(DC2Type:array)', UNIQUE INDEX UNIQ_B0F6A6D5A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, first_name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) DEFAULT NULL, agree_terms TINYINT(1) DEFAULT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, is_verified TINYINT(1) NOT NULL, verification_token VARCHAR(255) DEFAULT NULL, token_expiration DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', available_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', delivered_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE demande ADD CONSTRAINT FK_2694D7A5727ACA70 FOREIGN KEY (parent_id) REFERENCES parent_entity (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE demande ADD CONSTRAINT FK_2694D7A5CB944F1A FOREIGN KEY (student_id) REFERENCES student (id) ON DELETE SET NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE demande ADD CONSTRAINT FK_2694D7A5F5F311B0 FOREIGN KEY (target_study_class_id) REFERENCES study_class (id) ON DELETE SET NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE invoice ADD CONSTRAINT FK_90651744727ACA70 FOREIGN KEY (parent_id) REFERENCES parent_entity (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE parent_entity ADD CONSTRAINT FK_413B87AEA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE payment ADD CONSTRAINT FK_6D28840D727ACA70 FOREIGN KEY (parent_id) REFERENCES parent_entity (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE payment ADD CONSTRAINT FK_6D28840DCB944F1A FOREIGN KEY (student_id) REFERENCES student (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE payment ADD CONSTRAINT FK_6D28840D49891E99 FOREIGN KEY (study_class_id) REFERENCES study_class (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE payment ADD CONSTRAINT FK_6D28840D2989F1FD FOREIGN KEY (invoice_id) REFERENCES invoice (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE payment ADD CONSTRAINT FK_6D28840D2FFD4FD3 FOREIGN KEY (processed_by_id) REFERENCES user (id) ON DELETE SET NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE payment_book_item ADD CONSTRAINT FK_45AF413E4C3A3BB FOREIGN KEY (payment_id) REFERENCES payment (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE payment_book_item ADD CONSTRAINT FK_45AF413E16A2B381 FOREIGN KEY (book_id) REFERENCES book (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE registration_arabic_cours ADD CONSTRAINT FK_37E83FD9CB944F1A FOREIGN KEY (student_id) REFERENCES student (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE session ADD CONSTRAINT FK_D044D5D454177093 FOREIGN KEY (room_id) REFERENCES room (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE session ADD CONSTRAINT FK_D044D5D449891E99 FOREIGN KEY (study_class_id) REFERENCES study_class (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE session ADD CONSTRAINT FK_D044D5D441807E1D FOREIGN KEY (teacher_id) REFERENCES teacher (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE session_study_class_presence ADD CONSTRAINT FK_2E87B39CCB944F1A FOREIGN KEY (student_id) REFERENCES student (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE session_study_class_presence ADD CONSTRAINT FK_2E87B39C613FECDF FOREIGN KEY (session_id) REFERENCES session (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE student ADD CONSTRAINT FK_B723AF33727ACA70 FOREIGN KEY (parent_id) REFERENCES parent_entity (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE student_class_registered ADD CONSTRAINT FK_2FA232B7CB944F1A FOREIGN KEY (student_id) REFERENCES student (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE student_class_registered ADD CONSTRAINT FK_2FA232B749891E99 FOREIGN KEY (study_class_id) REFERENCES study_class (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE study_class ADD CONSTRAINT FK_B231497F98BFDD30 FOREIGN KEY (principal_teacher_id) REFERENCES teacher (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE study_class ADD CONSTRAINT FK_B231497FBF78C012 FOREIGN KEY (principal_room_id) REFERENCES room (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE teacher ADD CONSTRAINT FK_B0F6A6D5A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE demande DROP FOREIGN KEY FK_2694D7A5727ACA70
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE demande DROP FOREIGN KEY FK_2694D7A5CB944F1A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE demande DROP FOREIGN KEY FK_2694D7A5F5F311B0
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE invoice DROP FOREIGN KEY FK_90651744727ACA70
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE parent_entity DROP FOREIGN KEY FK_413B87AEA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE payment DROP FOREIGN KEY FK_6D28840D727ACA70
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE payment DROP FOREIGN KEY FK_6D28840DCB944F1A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE payment DROP FOREIGN KEY FK_6D28840D49891E99
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE payment DROP FOREIGN KEY FK_6D28840D2989F1FD
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE payment DROP FOREIGN KEY FK_6D28840D2FFD4FD3
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE payment_book_item DROP FOREIGN KEY FK_45AF413E4C3A3BB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE payment_book_item DROP FOREIGN KEY FK_45AF413E16A2B381
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE registration_arabic_cours DROP FOREIGN KEY FK_37E83FD9CB944F1A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE session DROP FOREIGN KEY FK_D044D5D454177093
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE session DROP FOREIGN KEY FK_D044D5D449891E99
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE session DROP FOREIGN KEY FK_D044D5D441807E1D
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE session_study_class_presence DROP FOREIGN KEY FK_2E87B39CCB944F1A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE session_study_class_presence DROP FOREIGN KEY FK_2E87B39C613FECDF
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE student DROP FOREIGN KEY FK_B723AF33727ACA70
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE student_class_registered DROP FOREIGN KEY FK_2FA232B7CB944F1A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE student_class_registered DROP FOREIGN KEY FK_2FA232B749891E99
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE study_class DROP FOREIGN KEY FK_B231497F98BFDD30
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE study_class DROP FOREIGN KEY FK_B231497FBF78C012
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE teacher DROP FOREIGN KEY FK_B0F6A6D5A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE academic_support_registration
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE book
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE contact
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE demande
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE invoice
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE parent_entity
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE payment
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE payment_book_item
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE registration_arabic_cours
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE room
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE session
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE session_study_class_presence
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE student
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE student_class_registered
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE study_class
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE teacher
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
    }
}
