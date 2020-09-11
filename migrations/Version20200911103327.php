<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200911103327 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_message_filters ADD territorial_council_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          adherent_message_filters 
        ADD 
          CONSTRAINT FK_28CA9F94AAA61A99 FOREIGN KEY (territorial_council_id) REFERENCES territorial_council (id)');
        $this->addSql('CREATE INDEX IDX_28CA9F94AAA61A99 ON adherent_message_filters (territorial_council_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_message_filters DROP FOREIGN KEY FK_28CA9F94AAA61A99');
        $this->addSql('DROP INDEX IDX_28CA9F94AAA61A99 ON adherent_message_filters');
        $this->addSql('ALTER TABLE adherent_message_filters DROP territorial_council_id');
    }
}
