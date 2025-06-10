<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250610140635 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE songbook_chordsheet (id INT AUTO_INCREMENT NOT NULL, songbook_id INT NOT NULL, chordsheet_id INT NOT NULL, added_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', position INT DEFAULT NULL, INDEX IDX_3085BDE9E9EA4588 (songbook_id), INDEX IDX_3085BDE94C3CD4DB (chordsheet_id), UNIQUE INDEX UNIQ_SONGBOOK_CHORDSHEET (songbook_id, chordsheet_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE songbook_chordsheet ADD CONSTRAINT FK_3085BDE9E9EA4588 FOREIGN KEY (songbook_id) REFERENCES songbook (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE songbook_chordsheet ADD CONSTRAINT FK_3085BDE94C3CD4DB FOREIGN KEY (chordsheet_id) REFERENCES chordsheet (id) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE songbook_chordsheet DROP FOREIGN KEY FK_3085BDE9E9EA4588
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE songbook_chordsheet DROP FOREIGN KEY FK_3085BDE94C3CD4DB
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE songbook_chordsheet
        SQL);
    }
}
