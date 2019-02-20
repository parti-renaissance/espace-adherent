<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20170328230144 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE procuration_proxies ADD disabled TINYINT(1) DEFAULT 0');
        $this->addSql('UPDATE procuration_proxies SET disabled = 0');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE procuration_proxies DROP disabled');
    }
}
