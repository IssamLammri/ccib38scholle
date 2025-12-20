<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251016173249 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE refund (id INT AUTO_INCREMENT NOT NULL, parent_id INT NOT NULL, amount NUMERIC(10, 2) NOT NULL, refund_date DATETIME NOT NULL, method VARCHAR(50) DEFAULT NULL, status VARCHAR(20) NOT NULL, comment VARCHAR(255) DEFAULT NULL, reference VARCHAR(100) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_5B2C1458727ACA70 (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE refund_invoice (refund_id INT NOT NULL, invoice_id INT NOT NULL, INDEX IDX_CED29D00189801D5 (refund_id), INDEX IDX_CED29D002989F1FD (invoice_id), PRIMARY KEY(refund_id, invoice_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE refund ADD CONSTRAINT FK_5B2C1458727ACA70 FOREIGN KEY (parent_id) REFERENCES parent_entity (id)');
        $this->addSql('ALTER TABLE refund_invoice ADD CONSTRAINT FK_CED29D00189801D5 FOREIGN KEY (refund_id) REFERENCES refund (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE refund_invoice ADD CONSTRAINT FK_CED29D002989F1FD FOREIGN KEY (invoice_id) REFERENCES invoice (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE academic_support_registration CHANGE subjects subjects JSON NOT NULL');
        $this->addSql('ALTER TABLE contact CHANGE tags tags JSON NOT NULL, CHANGE segments segments JSON NOT NULL');
        $this->addSql('ALTER TABLE registration_arabic_cours CHANGE authorized authorized JSON NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE roles roles JSON NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE refund DROP FOREIGN KEY FK_5B2C1458727ACA70');
        $this->addSql('ALTER TABLE refund_invoice DROP FOREIGN KEY FK_CED29D00189801D5');
        $this->addSql('ALTER TABLE refund_invoice DROP FOREIGN KEY FK_CED29D002989F1FD');
        $this->addSql('DROP TABLE refund');
        $this->addSql('DROP TABLE refund_invoice');
        $this->addSql('ALTER TABLE academic_support_registration CHANGE subjects subjects LONGTEXT NOT NULL COLLATE `utf8mb4_bin`');
        $this->addSql('ALTER TABLE contact CHANGE tags tags LONGTEXT NOT NULL COLLATE `utf8mb4_bin`, CHANGE segments segments LONGTEXT NOT NULL COLLATE `utf8mb4_bin`');
        $this->addSql('ALTER TABLE registration_arabic_cours CHANGE authorized authorized LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_bin`');
        $this->addSql('ALTER TABLE user CHANGE roles roles LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_bin`');
    }
}
