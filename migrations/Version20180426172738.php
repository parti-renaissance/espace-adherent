<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20180426172738 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<SQL
CREATE TABLE committee_referent_tag 
(
    committee_id INT UNSIGNED NOT NULL,
    referent_tag_id INT UNSIGNED NOT NULL,
    INDEX IDX_285EB1C5ED1A100B (committee_id),
    INDEX IDX_285EB1C59C262DB3 (referent_tag_id),
    PRIMARY KEY(committee_id, referent_tag_id)
) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
SQL
        );
        $this->addSql(<<<SQL
CREATE TABLE event_referent_tag 
(
  event_id INT UNSIGNED NOT NULL,
  referent_tag_id INT UNSIGNED NOT NULL,
  INDEX IDX_D3C8F5BE71F7E88B (event_id),
  INDEX IDX_D3C8F5BE9C262DB3 (referent_tag_id),
  PRIMARY KEY(event_id, referent_tag_id)
) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
SQL
        );
        $this->addSql('ALTER TABLE committee_referent_tag ADD CONSTRAINT FK_285EB1C5ED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE committee_referent_tag ADD CONSTRAINT FK_285EB1C59C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event_referent_tag ADD CONSTRAINT FK_D3C8F5BE71F7E88B FOREIGN KEY (event_id) REFERENCES events (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event_referent_tag ADD CONSTRAINT FK_D3C8F5BE9C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE committee_referent_tag');
        $this->addSql('DROP TABLE event_referent_tag');
    }
}
