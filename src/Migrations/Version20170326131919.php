<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20170326131919 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE procuration_proxies ADD procuration_request_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE procuration_proxies ADD CONSTRAINT FK_9B5E777A128D9C53 FOREIGN KEY (procuration_request_id) REFERENCES procuration_requests (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9B5E777A128D9C53 ON procuration_proxies (procuration_request_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE procuration_proxies DROP FOREIGN KEY FK_9B5E777A128D9C53');
        $this->addSql('DROP INDEX UNIQ_9B5E777A128D9C53 ON procuration_proxies');
        $this->addSql('ALTER TABLE procuration_proxies DROP procuration_request_id');
    }
}
