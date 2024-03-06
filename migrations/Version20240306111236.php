<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240306111236 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE procuration_v2_proxies ADD round_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE
          procuration_v2_proxies
        ADD
          CONSTRAINT FK_4D04EBA4A6005CA0 FOREIGN KEY (round_id) REFERENCES procuration_v2_rounds (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_4D04EBA4A6005CA0 ON procuration_v2_proxies (round_id)');
        $this->addSql('ALTER TABLE procuration_v2_requests ADD round_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE
          procuration_v2_requests
        ADD
          CONSTRAINT FK_F6D458CBA6005CA0 FOREIGN KEY (round_id) REFERENCES procuration_v2_rounds (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_F6D458CBA6005CA0 ON procuration_v2_requests (round_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE procuration_v2_proxies DROP FOREIGN KEY FK_4D04EBA4A6005CA0');
        $this->addSql('DROP INDEX IDX_4D04EBA4A6005CA0 ON procuration_v2_proxies');
        $this->addSql('ALTER TABLE procuration_v2_proxies DROP round_id');
        $this->addSql('ALTER TABLE procuration_v2_requests DROP FOREIGN KEY FK_F6D458CBA6005CA0');
        $this->addSql('DROP INDEX IDX_F6D458CBA6005CA0 ON procuration_v2_requests');
        $this->addSql('ALTER TABLE procuration_v2_requests DROP round_id');
    }
}
