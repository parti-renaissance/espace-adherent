<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240314121122 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE procuration_v2_proxies DROP FOREIGN KEY FK_4D04EBA4149E6033');
        $this->addSql('ALTER TABLE procuration_v2_proxies DROP FOREIGN KEY FK_4D04EBA4A6005CA0');
        $this->addSql('ALTER TABLE procuration_v2_proxies DROP FOREIGN KEY FK_4D04EBA4F3F90B30');
        $this->addSql('ALTER TABLE
          procuration_v2_proxies
        CHANGE
          vote_place_id vote_place_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          procuration_v2_proxies
        ADD
          CONSTRAINT FK_4D04EBA4149E6033 FOREIGN KEY (vote_zone_id) REFERENCES geo_zone (id)');
        $this->addSql('ALTER TABLE
          procuration_v2_proxies
        ADD
          CONSTRAINT FK_4D04EBA4A6005CA0 FOREIGN KEY (round_id) REFERENCES procuration_v2_rounds (id)');
        $this->addSql('ALTER TABLE
          procuration_v2_proxies
        ADD
          CONSTRAINT FK_4D04EBA4F3F90B30 FOREIGN KEY (vote_place_id) REFERENCES geo_zone (id)');
        $this->addSql('ALTER TABLE procuration_v2_requests DROP FOREIGN KEY FK_F6D458CB149E6033');
        $this->addSql('ALTER TABLE procuration_v2_requests DROP FOREIGN KEY FK_F6D458CBA6005CA0');
        $this->addSql('ALTER TABLE procuration_v2_requests DROP FOREIGN KEY FK_F6D458CBF3F90B30');
        $this->addSql('ALTER TABLE
          procuration_v2_requests
        CHANGE
          vote_place_id vote_place_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          procuration_v2_requests
        ADD
          CONSTRAINT FK_F6D458CB149E6033 FOREIGN KEY (vote_zone_id) REFERENCES geo_zone (id)');
        $this->addSql('ALTER TABLE
          procuration_v2_requests
        ADD
          CONSTRAINT FK_F6D458CBA6005CA0 FOREIGN KEY (round_id) REFERENCES procuration_v2_rounds (id)');
        $this->addSql('ALTER TABLE
          procuration_v2_requests
        ADD
          CONSTRAINT FK_F6D458CBF3F90B30 FOREIGN KEY (vote_place_id) REFERENCES geo_zone (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE procuration_v2_proxies DROP FOREIGN KEY FK_4D04EBA4149E6033');
        $this->addSql('ALTER TABLE procuration_v2_proxies DROP FOREIGN KEY FK_4D04EBA4F3F90B30');
        $this->addSql('ALTER TABLE procuration_v2_proxies DROP FOREIGN KEY FK_4D04EBA4A6005CA0');
        $this->addSql('ALTER TABLE procuration_v2_proxies CHANGE vote_place_id vote_place_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE
          procuration_v2_proxies
        ADD
          CONSTRAINT FK_4D04EBA4149E6033 FOREIGN KEY (vote_zone_id) REFERENCES geo_zone (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          procuration_v2_proxies
        ADD
          CONSTRAINT FK_4D04EBA4F3F90B30 FOREIGN KEY (vote_place_id) REFERENCES geo_zone (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          procuration_v2_proxies
        ADD
          CONSTRAINT FK_4D04EBA4A6005CA0 FOREIGN KEY (round_id) REFERENCES procuration_v2_rounds (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE procuration_v2_requests DROP FOREIGN KEY FK_F6D458CB149E6033');
        $this->addSql('ALTER TABLE procuration_v2_requests DROP FOREIGN KEY FK_F6D458CBF3F90B30');
        $this->addSql('ALTER TABLE procuration_v2_requests DROP FOREIGN KEY FK_F6D458CBA6005CA0');
        $this->addSql('ALTER TABLE procuration_v2_requests CHANGE vote_place_id vote_place_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE
          procuration_v2_requests
        ADD
          CONSTRAINT FK_F6D458CB149E6033 FOREIGN KEY (vote_zone_id) REFERENCES geo_zone (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          procuration_v2_requests
        ADD
          CONSTRAINT FK_F6D458CBF3F90B30 FOREIGN KEY (vote_place_id) REFERENCES geo_zone (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          procuration_v2_requests
        ADD
          CONSTRAINT FK_F6D458CBA6005CA0 FOREIGN KEY (round_id) REFERENCES procuration_v2_rounds (id) ON UPDATE NO ACTION ON DELETE CASCADE');
    }
}
