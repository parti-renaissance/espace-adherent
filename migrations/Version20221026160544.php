<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221026160544 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          adherent_request
        ADD
          utm_source VARCHAR(255) DEFAULT NULL,
        ADD
          utm_campaign VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE
          adherents
        ADD
          utm_source VARCHAR(255) DEFAULT NULL,
        ADD
          utm_campaign VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_request DROP utm_source, DROP utm_campaign');
        $this->addSql('ALTER TABLE adherents DROP utm_source, DROP utm_campaign');
    }
}
