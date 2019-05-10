<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190227153320 extends AbstractMigration
{
    private $procurationMatches = [];

    public function preUp(Schema $schema)
    {
        foreach ($this->connection->fetchAll('SELECT id AS procuration_proxy_id, procuration_request_id, vote_country FROM procuration_proxies WHERE procuration_request_id IS NOT NULL') as $procurationProxy) {
            $this->procurationMatches[] = [
                'procuration_proxy_id' => $procurationProxy['procuration_proxy_id'],
                'procuration_request_id' => $procurationProxy['procuration_request_id'],
                'vote_country' => $procurationProxy['vote_country'],
            ];
        }
    }

    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE procuration_proxies SET procuration_request_id = NULL WHERE procuration_request_id IS NOT NULL');
        $this->addSql('ALTER TABLE procuration_proxies DROP FOREIGN KEY FK_9B5E777A128D9C53');
        $this->addSql('DROP INDEX UNIQ_9B5E777A128D9C53 ON procuration_proxies');
        $this->addSql('ALTER TABLE procuration_proxies DROP procuration_request_id');
        $this->addSql('ALTER TABLE procuration_requests ADD found_proxy_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          procuration_requests 
        ADD 
          CONSTRAINT FK_9769FD842F1B6663 FOREIGN KEY (found_proxy_id) REFERENCES procuration_proxies (id)');
        $this->addSql('CREATE INDEX IDX_9769FD842F1B6663 ON procuration_requests (found_proxy_id)');
        $this->addSql('ALTER TABLE 
          procuration_proxies 
        ADD 
          french_request_available TINYINT(1) DEFAULT \'1\' NOT NULL, 
        ADD 
          foreign_request_available TINYINT(1) DEFAULT \'1\' NOT NULL');
        $this->addSql('ALTER TABLE procuration_requests ADD request_from_france TINYINT(1) DEFAULT \'1\' NOT NULL');
    }

    public function postUp(Schema $schema)
    {
        foreach ($this->procurationMatches as $procurationMatch) {
            $this->connection->executeUpdate(
                sprintf('UPDATE procuration_requests SET found_proxy_id = %d WHERE id = %d',
                    $procurationMatch['procuration_proxy_id'],
                    $procurationMatch['procuration_request_id']
                )
            );

            $columnToUpdate = 'FR' === $procurationMatch['vote_country']
                ? 'french_request_available'
                : 'foreign_request_available'
            ;

            $this->connection->executeUpdate(
                sprintf("UPDATE procuration_proxies SET $columnToUpdate = 0 WHERE id = %d",
                    $procurationMatch['procuration_proxy_id']
                )
            );
        }
    }

    public function preDown(Schema $schema)
    {
        foreach ($this->connection->fetchAll('SELECT id AS procuration_request_id, found_proxy_id AS procuration_proxy_id FROM procuration_requests WHERE found_proxy_id IS NOT NULL') as $procurationRequest) {
            $this->procurationMatches[] = [
                'procuration_proxy_id' => $procurationRequest['procuration_proxy_id'],
                'procuration_request_id' => $procurationRequest['procuration_request_id'],
            ];
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE procuration_proxies ADD procuration_request_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          procuration_proxies 
        ADD 
          CONSTRAINT FK_9B5E777A128D9C53 FOREIGN KEY (procuration_request_id) REFERENCES procuration_requests (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9B5E777A128D9C53 ON procuration_proxies (procuration_request_id)');
        $this->addSql('ALTER TABLE procuration_requests DROP FOREIGN KEY FK_9769FD842F1B6663');
        $this->addSql('DROP INDEX IDX_9769FD842F1B6663 ON procuration_requests');
        $this->addSql('ALTER TABLE procuration_requests DROP found_proxy_id');
        $this->addSql('ALTER TABLE procuration_proxies DROP french_request_available, DROP foreign_request_available');
        $this->addSql('ALTER TABLE procuration_requests DROP request_from_france');
    }

    public function postDown(Schema $schema)
    {
        foreach ($this->procurationMatches as $procurationMatch) {
            $this->connection->executeUpdate(
                sprintf('UPDATE procuration_proxies SET procuration_request_id = %d WHERE id = %d',
                    $procurationMatch['procuration_request_id'],
                    $procurationMatch['procuration_proxy_id']
                )
            );
        }
    }
}
