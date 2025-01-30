<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240724111618 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE committee_merge_histories DROP FOREIGN KEY FK_BB95FBBC3BF0CCB3');
        $this->addSql('ALTER TABLE committee_merge_histories DROP FOREIGN KEY FK_BB95FBBC50FA8329');
        $this->addSql('ALTER TABLE committee_merge_histories DROP FOREIGN KEY FK_BB95FBBCA8E1562');
        $this->addSql('ALTER TABLE committee_merge_histories DROP FOREIGN KEY FK_BB95FBBC5C34CBC4');
        $this->addSql('ALTER TABLE committee_merge_histories_merged_memberships DROP FOREIGN KEY FK_CB8E336F9379ED92');
        $this->addSql('ALTER TABLE committee_merge_histories_merged_memberships DROP FOREIGN KEY FK_CB8E336FFCC6DA91');
        $this->addSql('DROP TABLE committee_merge_histories');
        $this->addSql('DROP TABLE committee_merge_histories_merged_memberships');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE committee_merge_histories (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          source_committee_id INT UNSIGNED NOT NULL,
          destination_committee_id INT UNSIGNED NOT NULL,
          merged_by_id INT DEFAULT NULL,
          reverted_by_id INT DEFAULT NULL,
          date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          reverted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          INDEX committee_merge_histories_date_idx (date),
          INDEX committee_merge_histories_destination_committee_id_idx (destination_committee_id),
          INDEX committee_merge_histories_source_committee_id_idx (source_committee_id),
          INDEX IDX_BB95FBBC50FA8329 (merged_by_id),
          INDEX IDX_BB95FBBCA8E1562 (reverted_by_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE committee_merge_histories_merged_memberships (
          committee_merge_history_id INT UNSIGNED NOT NULL,
          committee_membership_id INT UNSIGNED NOT NULL,
          UNIQUE INDEX UNIQ_CB8E336FFCC6DA91 (committee_membership_id),
          INDEX IDX_CB8E336F9379ED92 (committee_merge_history_id),
          PRIMARY KEY(
            committee_merge_history_id, committee_membership_id
          )
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('ALTER TABLE
          committee_merge_histories
        ADD
          CONSTRAINT FK_BB95FBBC3BF0CCB3 FOREIGN KEY (source_committee_id) REFERENCES committees (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          committee_merge_histories
        ADD
          CONSTRAINT FK_BB95FBBC50FA8329 FOREIGN KEY (merged_by_id) REFERENCES administrators (id) ON UPDATE NO ACTION ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          committee_merge_histories
        ADD
          CONSTRAINT FK_BB95FBBCA8E1562 FOREIGN KEY (reverted_by_id) REFERENCES administrators (id) ON UPDATE NO ACTION ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          committee_merge_histories
        ADD
          CONSTRAINT FK_BB95FBBC5C34CBC4 FOREIGN KEY (destination_committee_id) REFERENCES committees (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          committee_merge_histories_merged_memberships
        ADD
          CONSTRAINT FK_CB8E336F9379ED92 FOREIGN KEY (committee_merge_history_id) REFERENCES committee_merge_histories (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          committee_merge_histories_merged_memberships
        ADD
          CONSTRAINT FK_CB8E336FFCC6DA91 FOREIGN KEY (committee_membership_id) REFERENCES committees_memberships (id) ON UPDATE NO ACTION ON DELETE CASCADE');
    }
}
