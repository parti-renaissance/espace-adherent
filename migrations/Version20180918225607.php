<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20180918225607 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
          CREATE TABLE committee_merge_histories
          (
            id INT UNSIGNED AUTO_INCREMENT NOT NULL,
            source_committee_id INT UNSIGNED NOT NULL,
            destination_committee_id INT UNSIGNED NOT NULL,
            date DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
            INDEX committee_merge_histories_source_committee_id_idx (source_committee_id),
            INDEX committee_merge_histories_destination_committee_id_idx (destination_committee_id),
            INDEX committee_merge_histories_date_idx (date),
            PRIMARY KEY(id)
          ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB
SQL
        );
        $this->addSql('ALTER TABLE committee_merge_histories ADD CONSTRAINT FK_BB95FBBC3BF0CCB3 FOREIGN KEY (source_committee_id) REFERENCES committees (id)');
        $this->addSql('ALTER TABLE committee_merge_histories ADD CONSTRAINT FK_BB95FBBC5C34CBC4 FOREIGN KEY (destination_committee_id) REFERENCES committees (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE committee_merge_histories');
    }
}
