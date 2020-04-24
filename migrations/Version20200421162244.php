<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200421162244 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE committee_merge_histories_merged_memberships (
          committee_merge_history_id INT UNSIGNED NOT NULL, 
          committee_membership_id INT UNSIGNED NOT NULL, 
          INDEX IDX_CB8E336F9379ED92 (committee_merge_history_id), 
          UNIQUE INDEX UNIQ_CB8E336FFCC6DA91 (committee_membership_id), 
          PRIMARY KEY(
            committee_merge_history_id, committee_membership_id
          )
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          committee_merge_histories_merged_memberships 
        ADD 
          CONSTRAINT FK_CB8E336F9379ED92 FOREIGN KEY (committee_merge_history_id) REFERENCES committee_merge_histories (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          committee_merge_histories_merged_memberships 
        ADD 
          CONSTRAINT FK_CB8E336FFCC6DA91 FOREIGN KEY (committee_membership_id) REFERENCES committees_memberships (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE committee_merge_histories DROP FOREIGN KEY FK_BB95FBBC50FA8329');
        $this->addSql('ALTER TABLE 
          committee_merge_histories 
        ADD 
          reverted_by_id INT DEFAULT NULL, 
        ADD 
          reverted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE 
          committee_merge_histories 
        ADD 
          CONSTRAINT FK_BB95FBBCA8E1562 FOREIGN KEY (reverted_by_id) REFERENCES administrators (id) ON DELETE 
        SET 
          NULL');
        $this->addSql('ALTER TABLE 
          committee_merge_histories 
        ADD 
          CONSTRAINT FK_BB95FBBC50FA8329 FOREIGN KEY (merged_by_id) REFERENCES administrators (id) ON DELETE 
        SET 
          NULL');
        $this->addSql('CREATE INDEX IDX_BB95FBBCA8E1562 ON committee_merge_histories (reverted_by_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE committee_merge_histories_merged_memberships');
        $this->addSql('ALTER TABLE committee_merge_histories DROP FOREIGN KEY FK_BB95FBBCA8E1562');
        $this->addSql('ALTER TABLE committee_merge_histories DROP FOREIGN KEY FK_BB95FBBC50FA8329');
        $this->addSql('DROP INDEX IDX_BB95FBBCA8E1562 ON committee_merge_histories');
        $this->addSql('ALTER TABLE committee_merge_histories DROP reverted_by_id, DROP reverted_at');
        $this->addSql('ALTER TABLE 
          committee_merge_histories 
        ADD 
          CONSTRAINT FK_BB95FBBC50FA8329 FOREIGN KEY (merged_by_id) REFERENCES administrators (id)');
    }
}
