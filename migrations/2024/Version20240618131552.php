<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240618131552 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE procuration_v2_requests DROP FOREIGN KEY FK_F6D458CBDB26A4E');
        $this->addSql('DROP INDEX IDX_F6D458CBDB26A4E ON procuration_v2_requests');
        $this->addSql('ALTER TABLE procuration_v2_requests DROP proxy_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE procuration_v2_requests ADD proxy_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          procuration_v2_requests
        ADD
          CONSTRAINT FK_F6D458CBDB26A4E FOREIGN KEY (proxy_id) REFERENCES procuration_v2_proxies (id) ON UPDATE NO ACTION ON DELETE
        SET
          NULL');
        $this->addSql('CREATE INDEX IDX_F6D458CBDB26A4E ON procuration_v2_requests (proxy_id)');
    }
}
