<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190327135856 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_message_filters ADD committee_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          adherent_message_filters 
        ADD 
          CONSTRAINT FK_28CA9F94ED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id)');
        $this->addSql('CREATE INDEX IDX_28CA9F94ED1A100B ON adherent_message_filters (committee_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_message_filters DROP FOREIGN KEY FK_28CA9F94ED1A100B');
        $this->addSql('DROP INDEX IDX_28CA9F94ED1A100B ON adherent_message_filters');
        $this->addSql('ALTER TABLE adherent_message_filters DROP committee_id');
    }
}
