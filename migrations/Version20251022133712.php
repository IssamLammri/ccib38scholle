<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251022133712 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE refund DROP FOREIGN KEY FK_5B2C1458727ACA70
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE refund ADD CONSTRAINT FK_5B2C1458727ACA70 FOREIGN KEY (parent_id) REFERENCES parent_entity (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE session DROP FOREIGN KEY FK_D044D5D454177093
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE session CHANGE room_id room_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE session ADD CONSTRAINT FK_D044D5D454177093 FOREIGN KEY (room_id) REFERENCES room (id) ON DELETE SET NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE session_study_class_presence DROP FOREIGN KEY FK_2E87B39C613FECDF
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE session_study_class_presence CHANGE session_id session_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE session_study_class_presence ADD CONSTRAINT FK_2E87B39C613FECDF FOREIGN KEY (session_id) REFERENCES session (id) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE refund DROP FOREIGN KEY FK_5B2C1458727ACA70
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE refund ADD CONSTRAINT FK_5B2C1458727ACA70 FOREIGN KEY (parent_id) REFERENCES parent_entity (id) ON UPDATE NO ACTION ON DELETE NO ACTION
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE session DROP FOREIGN KEY FK_D044D5D454177093
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE session CHANGE room_id room_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE session ADD CONSTRAINT FK_D044D5D454177093 FOREIGN KEY (room_id) REFERENCES room (id) ON UPDATE NO ACTION ON DELETE NO ACTION
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE session_study_class_presence DROP FOREIGN KEY FK_2E87B39C613FECDF
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE session_study_class_presence CHANGE session_id session_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE session_study_class_presence ADD CONSTRAINT FK_2E87B39C613FECDF FOREIGN KEY (session_id) REFERENCES session (id) ON UPDATE NO ACTION ON DELETE NO ACTION
        SQL);
    }
}
