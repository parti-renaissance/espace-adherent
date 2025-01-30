<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220616095027 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('DROP TABLE republican_silence_referent_tag');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE republican_silence_referent_tag (
          republican_silence_id INT UNSIGNED NOT NULL,
          referent_tag_id INT UNSIGNED NOT NULL,
          INDEX IDX_543DED2612359909 (republican_silence_id),
          INDEX IDX_543DED269C262DB3 (referent_tag_id),
          PRIMARY KEY(
            republican_silence_id, referent_tag_id
          )
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('ALTER TABLE
          republican_silence_referent_tag
        ADD
          CONSTRAINT FK_543DED269C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          republican_silence_referent_tag
        ADD
          CONSTRAINT FK_543DED2612359909 FOREIGN KEY (republican_silence_id) REFERENCES republican_silence (id) ON UPDATE NO ACTION ON DELETE CASCADE');
    }
}
