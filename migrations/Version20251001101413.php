<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251001101413 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE academic_support_registration CHANGE subjects subjects JSON NOT NULL');
        $this->addSql('ALTER TABLE contact CHANGE tags tags JSON NOT NULL, CHANGE segments segments JSON NOT NULL');
        $this->addSql('ALTER TABLE payment ADD processed_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D2FFD4FD3 FOREIGN KEY (processed_by_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_6D28840D2FFD4FD3 ON payment (processed_by_id)');
        $this->addSql('ALTER TABLE registration_arabic_cours CHANGE authorized authorized JSON NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE roles roles JSON NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE academic_support_registration CHANGE subjects subjects LONGTEXT NOT NULL COLLATE `utf8mb4_bin`');
        $this->addSql('ALTER TABLE contact CHANGE tags tags LONGTEXT NOT NULL COLLATE `utf8mb4_bin`, CHANGE segments segments LONGTEXT NOT NULL COLLATE `utf8mb4_bin`');
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840D2FFD4FD3');
        $this->addSql('DROP INDEX IDX_6D28840D2FFD4FD3 ON payment');
        $this->addSql('ALTER TABLE payment DROP processed_by_id');
        $this->addSql('ALTER TABLE registration_arabic_cours CHANGE authorized authorized LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_bin`');
        $this->addSql('ALTER TABLE user CHANGE roles roles LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_bin`');
    }
}
