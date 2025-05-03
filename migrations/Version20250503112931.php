<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250503112931 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE registration_arabic_cours ADD previous_level VARCHAR(255) DEFAULT NULL, ADD sibling_enrolled VARCHAR(255) DEFAULT NULL, CHANGE authorized authorized JSON NOT NULL, CHANGE was_enrolled2024 was_enrolled2024 VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE roles roles JSON NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE registration_arabic_cours DROP previous_level, DROP sibling_enrolled, CHANGE authorized authorized LONGTEXT NOT NULL COLLATE `utf8mb4_bin`, CHANGE was_enrolled2024 was_enrolled2024 TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE roles roles LONGTEXT NOT NULL COLLATE `utf8mb4_bin`');
    }
}
