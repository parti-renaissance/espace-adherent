<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240308114452 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE procuration_v2_proxies ADD custom_vote_place VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE procuration_v2_requests ADD custom_vote_place VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE procuration_v2_proxies DROP custom_vote_place');
        $this->addSql('ALTER TABLE procuration_v2_requests DROP custom_vote_place');
    }
}
