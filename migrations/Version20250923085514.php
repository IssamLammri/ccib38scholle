<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250923085514 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE payment_book_item (id INT AUTO_INCREMENT NOT NULL, payment_id INT NOT NULL, book_id INT NOT NULL, quantity INT NOT NULL, unit_price NUMERIC(10, 2) NOT NULL, line_total NUMERIC(10, 2) NOT NULL, INDEX IDX_45AF413E4C3A3BB (payment_id), INDEX IDX_45AF413E16A2B381 (book_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE payment_book_item ADD CONSTRAINT FK_45AF413E4C3A3BB FOREIGN KEY (payment_id) REFERENCES payment (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE payment_book_item ADD CONSTRAINT FK_45AF413E16A2B381 FOREIGN KEY (book_id) REFERENCES book (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE payment_book_item DROP FOREIGN KEY FK_45AF413E4C3A3BB');
        $this->addSql('ALTER TABLE payment_book_item DROP FOREIGN KEY FK_45AF413E16A2B381');
        $this->addSql('DROP TABLE payment_book_item');
    }
}
