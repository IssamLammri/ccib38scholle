<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250918074425 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE contact (id INT AUTO_INCREMENT NOT NULL, public_token VARCHAR(64) NOT NULL, email VARCHAR(150) NOT NULL, first_name VARCHAR(100) DEFAULT NULL, last_name VARCHAR(100) DEFAULT NULL, phone VARCHAR(30) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, postal_code VARCHAR(12) DEFAULT NULL, city VARCHAR(150) DEFAULT NULL, locale VARCHAR(8) DEFAULT \'fr\' NOT NULL, tags JSON NOT NULL, segments JSON NOT NULL, consent_newsletter TINYINT(1) DEFAULT 0 NOT NULL, consent_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', consent_ip VARCHAR(45) DEFAULT NULL, consent_source VARCHAR(100) DEFAULT NULL, status VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', unsubscribed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', last_emailed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_4C62E638AE981E3B (public_token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE contact');
    }
}
