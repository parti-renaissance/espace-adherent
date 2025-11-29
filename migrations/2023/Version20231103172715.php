<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231103172715 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_message_filter_zone DROP FOREIGN KEY FK_64171C02B92CB468');
        $this->addSql('DROP INDEX IDX_64171C02B92CB468 ON adherent_message_filter_zone');
        $this->addSql('DROP INDEX `primary` ON adherent_message_filter_zone');
        $this->addSql('ALTER TABLE
          adherent_message_filter_zone
        CHANGE
          message_filter_id adherent_message_filter_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE
          adherent_message_filter_zone
        ADD
          CONSTRAINT FK_64171C02FBF331D5 FOREIGN KEY (adherent_message_filter_id) REFERENCES adherent_message_filters (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_64171C02FBF331D5 ON adherent_message_filter_zone (adherent_message_filter_id)');
        $this->addSql('ALTER TABLE adherent_message_filter_zone ADD PRIMARY KEY (adherent_message_filter_id, zone_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_message_filter_zone DROP FOREIGN KEY FK_64171C02FBF331D5');
        $this->addSql('DROP INDEX IDX_64171C02FBF331D5 ON adherent_message_filter_zone');
        $this->addSql('DROP INDEX `PRIMARY` ON adherent_message_filter_zone');
        $this->addSql('ALTER TABLE
          adherent_message_filter_zone
        CHANGE
          adherent_message_filter_id message_filter_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE
          adherent_message_filter_zone
        ADD
          CONSTRAINT FK_64171C02B92CB468 FOREIGN KEY (message_filter_id) REFERENCES adherent_message_filters (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_64171C02B92CB468 ON adherent_message_filter_zone (message_filter_id)');
        $this->addSql('ALTER TABLE adherent_message_filter_zone ADD PRIMARY KEY (message_filter_id, zone_id)');
    }
}
