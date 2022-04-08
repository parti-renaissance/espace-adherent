<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220408144824 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE procuration_proxies ADD reminded_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE procuration_requests ADD reminded_at DATETIME DEFAULT NULL');
        $this->addSql('UPDATE procuration_requests
        SET reminded_at = \'2022-04-04 11:00:00\'
        WHERE reminded = 1 AND created_at > \'2022-01-01 00:00:00\'');
        $this->addSql('ALTER TABLE procuration_requests DROP reminded');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE procuration_proxies DROP reminded_at');
        $this->addSql('ALTER TABLE procuration_requests ADD reminded INT NOT NULL, DROP reminded_at');
    }
}
