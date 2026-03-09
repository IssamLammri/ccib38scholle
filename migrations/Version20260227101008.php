<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260227101008 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE parent_amount_due_history (id INT AUTO_INCREMENT NOT NULL, parent_id INT NOT NULL, changed_by_id INT DEFAULT NULL, field VARCHAR(50) NOT NULL, old_value INT NOT NULL, new_value INT NOT NULL, comment LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_656422D2727ACA70 (parent_id), INDEX IDX_656422D2828AD0A0 (changed_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE parent_amount_due_history ADD CONSTRAINT FK_656422D2727ACA70 FOREIGN KEY (parent_id) REFERENCES parent_entity (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE parent_amount_due_history ADD CONSTRAINT FK_656422D2828AD0A0 FOREIGN KEY (changed_by_id) REFERENCES user (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE parent_amount_due_history DROP FOREIGN KEY FK_656422D2727ACA70');
        $this->addSql('ALTER TABLE parent_amount_due_history DROP FOREIGN KEY FK_656422D2828AD0A0');
        $this->addSql('DROP TABLE parent_amount_due_history');
    }
}
