<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250917182609 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE registration_arabic_cours ADD was_enrolled2024 VARCHAR(255) DEFAULT NULL, ADD previous_level VARCHAR(255) DEFAULT NULL, ADD sibling_enrolled VARCHAR(255) DEFAULT NULL, CHANGE token token VARCHAR(32) NOT NULL, CHANGE child_first_name child_first_name VARCHAR(100) NOT NULL, CHANGE child_last_name child_last_name VARCHAR(100) NOT NULL, CHANGE child_gender child_gender VARCHAR(10) NOT NULL, CHANGE child_level child_level VARCHAR(50) NOT NULL, CHANGE father_first_name father_first_name VARCHAR(100) NOT NULL, CHANGE father_last_name father_last_name VARCHAR(100) NOT NULL, CHANGE mother_first_name mother_first_name VARCHAR(100) NOT NULL, CHANGE mother_last_name mother_last_name VARCHAR(100) NOT NULL, CHANGE contact_email contact_email VARCHAR(180) NOT NULL, CHANGE father_phone father_phone VARCHAR(20) NOT NULL, CHANGE mother_phone mother_phone VARCHAR(20) NOT NULL, CHANGE address address VARCHAR(255) NOT NULL, CHANGE postal_code postal_code VARCHAR(10) NOT NULL, CHANGE city city VARCHAR(100) NOT NULL, CHANGE authorized authorized JSON NOT NULL, CHANGE authorized_other_name authorized_other_name VARCHAR(100) DEFAULT NULL, CHANGE authorized_other_relation authorized_other_relation VARCHAR(50) DEFAULT NULL, CHANGE leave_alone leave_alone VARCHAR(3) NOT NULL, CHANGE image_rights image_rights VARCHAR(3) NOT NULL, CHANGE medical_info medical_info VARCHAR(3) NOT NULL, CHANGE medical_details medical_details LONGTEXT DEFAULT NULL, CHANGE medical_treatment medical_treatment VARCHAR(3) NOT NULL, CHANGE child_photo_filename child_photo_filename VARCHAR(255) DEFAULT NULL, CHANGE step_registration step_registration VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE email email VARCHAR(180) NOT NULL, CHANGE first_name first_name VARCHAR(255) DEFAULT NULL, CHANGE last_name last_name VARCHAR(255) DEFAULT NULL, CHANGE roles roles JSON NOT NULL, CHANGE password password VARCHAR(255) NOT NULL, CHANGE verification_token verification_token VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE registration_arabic_cours DROP was_enrolled2024, DROP previous_level, DROP sibling_enrolled, CHANGE token token VARCHAR(32) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE child_first_name child_first_name VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE child_last_name child_last_name VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE child_gender child_gender VARCHAR(10) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE child_level child_level VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE father_first_name father_first_name VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE father_last_name father_last_name VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE mother_first_name mother_first_name VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE mother_last_name mother_last_name VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE contact_email contact_email VARCHAR(180) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE father_phone father_phone VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE mother_phone mother_phone VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE address address VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE postal_code postal_code VARCHAR(10) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE city city VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE authorized authorized LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_bin`, CHANGE authorized_other_name authorized_other_name VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE authorized_other_relation authorized_other_relation VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE leave_alone leave_alone VARCHAR(3) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE image_rights image_rights VARCHAR(3) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE medical_info medical_info VARCHAR(3) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE medical_details medical_details LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE medical_treatment medical_treatment VARCHAR(3) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE step_registration step_registration VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE child_photo_filename child_photo_filename VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE user CHANGE email email VARCHAR(180) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE first_name first_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE last_name last_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE roles roles LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_bin`, CHANGE password password VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE verification_token verification_token VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
