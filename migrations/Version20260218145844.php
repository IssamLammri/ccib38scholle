<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260218145844 extends AbstractMigration
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
        $this->addSql('ALTER TABLE parent_entity ADD amount_due_arabic INT DEFAULT 0 NOT NULL, ADD amount_due_soutien INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE registration_arabic_cours CHANGE authorized authorized JSON NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE roles roles JSON NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE academic_support_registration CHANGE subjects subjects LONGTEXT NOT NULL COLLATE `utf8mb4_bin`');
        $this->addSql('ALTER TABLE contact CHANGE tags tags LONGTEXT NOT NULL COLLATE `utf8mb4_bin`, CHANGE segments segments LONGTEXT NOT NULL COLLATE `utf8mb4_bin`');
        $this->addSql('ALTER TABLE parent_entity DROP amount_due_arabic, DROP amount_due_soutien');
        $this->addSql('ALTER TABLE registration_arabic_cours CHANGE authorized authorized LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_bin`');
        $this->addSql('ALTER TABLE user CHANGE roles roles LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_bin`');
    }
}
