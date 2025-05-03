<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250503113002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE registration_arabic_cours (id INT AUTO_INCREMENT NOT NULL, token VARCHAR(32) NOT NULL, child_first_name VARCHAR(100) NOT NULL, child_last_name VARCHAR(100) NOT NULL, child_dob DATE NOT NULL, child_gender VARCHAR(10) NOT NULL, child_level VARCHAR(50) NOT NULL, father_first_name VARCHAR(100) NOT NULL, father_last_name VARCHAR(100) NOT NULL, mother_first_name VARCHAR(100) NOT NULL, mother_last_name VARCHAR(100) NOT NULL, contact_email VARCHAR(180) NOT NULL, father_phone VARCHAR(20) NOT NULL, mother_phone VARCHAR(20) NOT NULL, address VARCHAR(255) NOT NULL, postal_code VARCHAR(10) NOT NULL, city VARCHAR(100) NOT NULL, authorized JSON NOT NULL, authorized_other_name VARCHAR(100) DEFAULT NULL, authorized_other_relation VARCHAR(50) DEFAULT NULL, leave_alone VARCHAR(3) NOT NULL, image_rights VARCHAR(3) NOT NULL, commitment_situ TINYINT(1) NOT NULL, medical_info VARCHAR(3) NOT NULL, medical_details LONGTEXT DEFAULT NULL, medical_treatment VARCHAR(3) NOT NULL, legal_declaration TINYINT(1) NOT NULL, payment_terms TINYINT(1) NOT NULL, child_photo_filename VARCHAR(255) DEFAULT NULL, was_enrolled2024 VARCHAR(255) DEFAULT NULL, previous_level VARCHAR(255) DEFAULT NULL, sibling_enrolled VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_37E83FD95F37A13B (token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user CHANGE roles roles JSON NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE registration_arabic_cours');
        $this->addSql('ALTER TABLE user CHANGE roles roles LONGTEXT NOT NULL COLLATE `utf8mb4_bin`');
    }
}
