<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240611175556 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE proxy_round (
          proxy_id INT UNSIGNED NOT NULL,
          round_id INT UNSIGNED NOT NULL,
          INDEX IDX_1C924019DB26A4E (proxy_id),
          INDEX IDX_1C924019A6005CA0 (round_id),
          PRIMARY KEY(proxy_id, round_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE request_round (
          request_id INT UNSIGNED NOT NULL,
          round_id INT UNSIGNED NOT NULL,
          INDEX IDX_98F95611427EB8A5 (request_id),
          INDEX IDX_98F95611A6005CA0 (round_id),
          PRIMARY KEY(request_id, round_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          proxy_round
        ADD
          CONSTRAINT FK_1C924019DB26A4E FOREIGN KEY (proxy_id) REFERENCES procuration_v2_proxies (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          proxy_round
        ADD
          CONSTRAINT FK_1C924019A6005CA0 FOREIGN KEY (round_id) REFERENCES procuration_v2_rounds (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          request_round
        ADD
          CONSTRAINT FK_98F95611427EB8A5 FOREIGN KEY (request_id) REFERENCES procuration_v2_requests (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          request_round
        ADD
          CONSTRAINT FK_98F95611A6005CA0 FOREIGN KEY (round_id) REFERENCES procuration_v2_rounds (id) ON DELETE CASCADE');

        $this->addSql(<<<'SQL'
                INSERT INTO proxy_round (proxy_id, round_id)
                SELECT
                    proxy.id,
                    round.id
                FROM procuration_v2_proxies AS proxy
                INNER JOIN procuration_v2_rounds AS round
                    ON round.id = proxy.round_id
                INNER JOIN procuration_v2_elections AS election
                    ON election.id = round.election_id
                WHERE election.slug = :election_slug
            SQL,
            [
                'election_slug' => 'europeennes',
            ]
        );
        $this->addSql(<<<'SQL'
                INSERT INTO request_round (request_id, round_id)
                SELECT
                    request.id,
                    round.id
                FROM procuration_v2_requests AS request
                INNER JOIN procuration_v2_rounds AS round
                    ON round.id = request.round_id
                INNER JOIN procuration_v2_elections AS election
                    ON election.id = round.election_id
                WHERE election.slug = :election_slug
            SQL,
            [
                'election_slug' => 'europeennes',
            ]
        );

        $this->addSql('ALTER TABLE procuration_v2_proxies DROP FOREIGN KEY FK_4D04EBA4A6005CA0');
        $this->addSql('DROP INDEX IDX_4D04EBA4A6005CA0 ON procuration_v2_proxies');
        $this->addSql('ALTER TABLE procuration_v2_proxies DROP round_id');
        $this->addSql('ALTER TABLE procuration_v2_requests DROP FOREIGN KEY FK_F6D458CBA6005CA0');
        $this->addSql('DROP INDEX IDX_F6D458CBA6005CA0 ON procuration_v2_requests');
        $this->addSql('ALTER TABLE procuration_v2_requests DROP round_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE proxy_round DROP FOREIGN KEY FK_1C924019DB26A4E');
        $this->addSql('ALTER TABLE proxy_round DROP FOREIGN KEY FK_1C924019A6005CA0');
        $this->addSql('ALTER TABLE request_round DROP FOREIGN KEY FK_98F95611427EB8A5');
        $this->addSql('ALTER TABLE request_round DROP FOREIGN KEY FK_98F95611A6005CA0');
        $this->addSql('DROP TABLE proxy_round');
        $this->addSql('DROP TABLE request_round');
        $this->addSql('ALTER TABLE procuration_v2_proxies ADD round_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE
          procuration_v2_proxies
        ADD
          CONSTRAINT FK_4D04EBA4A6005CA0 FOREIGN KEY (round_id) REFERENCES procuration_v2_rounds (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_4D04EBA4A6005CA0 ON procuration_v2_proxies (round_id)');
        $this->addSql('ALTER TABLE procuration_v2_requests ADD round_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE
          procuration_v2_requests
        ADD
          CONSTRAINT FK_F6D458CBA6005CA0 FOREIGN KEY (round_id) REFERENCES procuration_v2_rounds (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_F6D458CBA6005CA0 ON procuration_v2_requests (round_id)');
    }
}
