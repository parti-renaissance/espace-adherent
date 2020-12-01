<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20201201114334 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE jecoute_data_survey ADD device_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          jecoute_data_survey 
        ADD 
          CONSTRAINT FK_6579E8E794A4C7D4 FOREIGN KEY (device_id) REFERENCES devices (id) ON DELETE 
        SET 
          NULL');
        $this->addSql('CREATE INDEX IDX_6579E8E794A4C7D4 ON jecoute_data_survey (device_id)');
        $this->addSql('CREATE UNIQUE INDEX devices_device_uuid_unique ON devices (device_uuid)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX devices_device_uuid_unique ON devices');
        $this->addSql('ALTER TABLE jecoute_data_survey DROP FOREIGN KEY FK_6579E8E794A4C7D4');
        $this->addSql('DROP INDEX IDX_6579E8E794A4C7D4 ON jecoute_data_survey');
        $this->addSql('ALTER TABLE jecoute_data_survey DROP device_id');
    }
}
