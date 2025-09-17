<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250917132033 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE academic_support_registration (id INT AUTO_INCREMENT NOT NULL, public_token VARCHAR(64) NOT NULL, student_first_name VARCHAR(100) NOT NULL, student_last_name VARCHAR(100) NOT NULL, level VARCHAR(50) NOT NULL, subjects JSON NOT NULL, parent_first_name VARCHAR(100) NOT NULL, parent_last_name VARCHAR(100) NOT NULL, email VARCHAR(150) NOT NULL, phone VARCHAR(30) DEFAULT NULL, mother_first_name VARCHAR(100) DEFAULT NULL, mother_last_name VARCHAR(100) DEFAULT NULL, mother_phone VARCHAR(30) DEFAULT NULL, accepted_payment_terms TINYINT(1) NOT NULL, status VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_75CA9808AE981E3B (public_token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE academic_support_registration');
    }
}
