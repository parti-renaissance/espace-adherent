<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210727123010 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_message_filters ADD is_certified TINYINT(1) DEFAULT NULL');
        $this->addSql('CREATE TABLE audience_filter_referent_tag (
          audience_filter_id INT UNSIGNED NOT NULL,
          referent_tag_id INT UNSIGNED NOT NULL,
          INDEX IDX_85E75A86D903C892 (audience_filter_id),
          INDEX IDX_85E75A869C262DB3 (referent_tag_id),
          PRIMARY KEY(
            audience_filter_id, referent_tag_id
          )
        ) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          audience_filter_referent_tag
        ADD
          CONSTRAINT FK_85E75A86D903C892 FOREIGN KEY (audience_filter_id) REFERENCES adherent_message_filters (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          audience_filter_referent_tag
        ADD
          CONSTRAINT FK_85E75A869C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_message_filters DROP is_certified');
        $this->addSql('DROP TABLE audience_filter_referent_tag');
    }
}
