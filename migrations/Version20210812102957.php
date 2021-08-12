<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210812102957 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_message_filters ADD segment_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          adherent_message_filters
        ADD
          CONSTRAINT FK_28CA9F94DB296AAD FOREIGN KEY (segment_id) REFERENCES audience_segment (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_28CA9F94DB296AAD ON adherent_message_filters (segment_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_message_filters DROP FOREIGN KEY FK_28CA9F94DB296AAD');
        $this->addSql('DROP INDEX IDX_28CA9F94DB296AAD ON adherent_message_filters');
        $this->addSql('ALTER TABLE adherent_message_filters DROP segment_id');
    }
}
