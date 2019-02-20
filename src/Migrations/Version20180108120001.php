<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20180108120001 extends AbstractMigration
{
    private const REQUESTS_TABLE = 'procuration_requests';
    private const PROXIES_TABLE = 'procuration_proxies';
    private const REQUEST_JOIN_FIELD = 'procuration_request_id';
    private const PROXY_JOIN_FIELD = 'procuration_proxy_id';
    private const JOIN_FIELDS = [
        self::REQUESTS_TABLE => self::REQUEST_JOIN_FIELD,
        self::PROXIES_TABLE => self::PROXY_JOIN_FIELD,
    ];
    private const PRESIDENTIAL_FIRST_ROUND = 'election_presidential_first_round';
    private const PRESIDENTIAL_SECOND_ROUND = 'election_presidential_second_round';
    private const LEGISLATIVE_FIRST_ROUND = 'election_legislative_first_round';
    private const LEGISLATIVE_SECOND_ROUND = 'election_legislative_second_round';

    private $roundIds = [];
    private $requests = [];
    private $proposals = [];
    private $requestsToRounds = [];
    private $proposalsToRounds = [];

    public function preUp(Schema $schema): void
    {
        // Save legacy data
        $this->requests = $this->selectLegacyRounds(self::REQUESTS_TABLE);
        $this->proposals = $this->selectLegacyRounds(self::PROXIES_TABLE);
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE procuration_requests_to_election_rounds (procuration_request_id INT NOT NULL, election_round_id INT NOT NULL, INDEX IDX_A47BBD53128D9C53 (procuration_request_id), INDEX IDX_A47BBD53FCBF5E32 (election_round_id), PRIMARY KEY(procuration_request_id, election_round_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE procuration_proxies_to_election_rounds (procuration_proxy_id INT NOT NULL, election_round_id INT NOT NULL, INDEX IDX_D075F5A9E15E419B (procuration_proxy_id), INDEX IDX_D075F5A9FCBF5E32 (election_round_id), PRIMARY KEY(procuration_proxy_id, election_round_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE elections (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, introduction LONGTEXT NOT NULL, UNIQUE INDEX UNIQ_1BD26F335E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE election_rounds (id INT AUTO_INCREMENT NOT NULL, election_id INT NOT NULL, label VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, date DATE NOT NULL, INDEX IDX_37C02EA0A708DAFF (election_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE procuration_requests_to_election_rounds ADD CONSTRAINT FK_A47BBD53128D9C53 FOREIGN KEY (procuration_request_id) REFERENCES procuration_requests (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE procuration_requests_to_election_rounds ADD CONSTRAINT FK_A47BBD53FCBF5E32 FOREIGN KEY (election_round_id) REFERENCES election_rounds (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE procuration_proxies_to_election_rounds ADD CONSTRAINT FK_D075F5A9E15E419B FOREIGN KEY (procuration_proxy_id) REFERENCES procuration_proxies (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE procuration_proxies_to_election_rounds ADD CONSTRAINT FK_D075F5A9FCBF5E32 FOREIGN KEY (election_round_id) REFERENCES election_rounds (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE election_rounds ADD CONSTRAINT FK_37C02EA0A708DAFF FOREIGN KEY (election_id) REFERENCES elections (id)');
        $this->addSql('ALTER TABLE procuration_requests DROP election_presidential_first_round, DROP election_presidential_second_round, DROP election_legislative_first_round, DROP election_legislative_second_round');
        $this->addSql('ALTER TABLE procuration_proxies DROP election_presidential_first_round, DROP election_presidential_second_round, DROP election_legislative_first_round, DROP election_legislative_second_round');
    }

    public function postUp(Schema $schema): void
    {
        // Insert new data
        $presidentialsId = $this->insertElection('Élections Présidentielles 2017', <<<INTRODUCTION
<h1 class="text--larger">
    Chaque vote compte.
</h1>
<h2 class="text--medium b__nudge--top l__hide--on-mobile b__nudge--bottom-small">
    Les élections présidentielles ont lieu les 24 avril et 7 mai 2017 en France (15 et 29 avril pour les Français de l'étranger du continent Américain et 16 et 30 avril pour les autres Français de l'étranger).
</h2>
<div class="text--body">
    Si vous ne votez pas en France métropolitaine, <a href="https://www.service-public.fr/particuliers/actualites/A10598" class="link--white">renseignez-vous sur les dates</a>.
</div>
INTRODUCTION
        );
        $presidentialsFirstRoundId = $this->insertElectionRound(
            $presidentialsId,
            '1er tour des éléctions présidentielles 2017',
            'Dimanche 24 avril 2017 en France (15 avril pour les Français de l\'étranger du continent Américain et 16 avril pour les autres Français de l\'étranger)',
            '2017-04-24'
        );
        $presidentialsSecondRoundId = $this->insertElectionRound(
            $presidentialsId,
            '2e tour des éléctions présidentielles 2017',
            'Dimanche 7 mai 2017 en France (29 avril pour les Français de l\'étranger du continent Américain et 30 avril pour les autres Français de l\'étranger)',
            '2017-05-07'
        );
        $legislativesId = $this->insertElection('Élections Législatives 2017', <<<INTRODUCTION
<h1 class="text--larger">
    Chaque vote compte.
</h1>
<h2 class="text--medium b__nudge--top l__hide--on-mobile b__nudge--bottom-small">
    Les élections législatives ont lieu les 11 et 18 juin 2017 en France (3 et 17 juin pour les Français de l'étranger du continent Américain et 4 et 18 juin pour les autres Français de l'étranger).
</h2>
<div class="text--body">
    Si vous ne votez pas en France métropolitaine, <a href="https://www.service-public.fr/particuliers/actualites/A10598" class="link--white">renseignez-vous sur les dates</a>.
</div>
INTRODUCTION
        );
        $legislativesFirstRoundId = $this->insertElectionRound(
            $legislativesId,
            '1er tour des éléctions législatives 2017',
            'Dimanche 11 juin 2017 en France (3 juin pour les Français de l\'étranger du continent Américain et 4 juin pour les autres Français de l\'étranger).',
            '2017-06-11'
        );
        $legislativesSecondRoundId = $this->insertElectionRound(
            $legislativesId,
            '2e tour des éléctions législatives 2017',
            'Dimanche 18 juin 2017 en France (17 juin pour les Français de l\'étranger du continent Américain et 18 juin pour les autres Français de l\'étranger).',
            '2017-06-18'
        );

        // Keep new data ids
        $this->roundIds = [
            self::PRESIDENTIAL_FIRST_ROUND => $presidentialsFirstRoundId,
            self::PRESIDENTIAL_SECOND_ROUND => $presidentialsSecondRoundId,
            self::LEGISLATIVE_FIRST_ROUND => $legislativesFirstRoundId,
            self::LEGISLATIVE_SECOND_ROUND => $legislativesSecondRoundId,
        ];

        // Remap legacy with new data
        foreach ($this->requests as $request) {
            $this->linkNewDataToLegacy(self::REQUESTS_TABLE, $request);
        }

        foreach ($this->proposals as $proposal) {
            $this->linkNewDataToLegacy(self::PROXIES_TABLE, $proposal);
        }
    }

    public function preDown(Schema $schema): void
    {
        // Keep current data ids
        $this->roundIds = [
            $this->getRoundId('1er tour des éléctions présidentielles 2017') => self::PRESIDENTIAL_FIRST_ROUND,
            $this->getRoundId('2e tour des éléctions présidentielles 2017') => self::PRESIDENTIAL_SECOND_ROUND,
            $this->getRoundId('1er tour des éléctions législatives 2017') => self::LEGISLATIVE_FIRST_ROUND,
            $this->getRoundId('2e tour des éléctions législatives 2017') => self::LEGISLATIVE_SECOND_ROUND,
        ];

        // Save current data
        $this->requestsToRounds = $this->selectJoinedRounds(self::REQUESTS_TABLE);
        $this->proposalsToRounds = $this->selectJoinedRounds(self::PROXIES_TABLE);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE election_rounds DROP FOREIGN KEY FK_37C02EA0A708DAFF');
        $this->addSql('ALTER TABLE procuration_requests_to_election_rounds DROP FOREIGN KEY FK_A47BBD53FCBF5E32');
        $this->addSql('ALTER TABLE procuration_proxies_to_election_rounds DROP FOREIGN KEY FK_D075F5A9FCBF5E32');
        $this->addSql('DROP TABLE procuration_requests_to_election_rounds');
        $this->addSql('DROP TABLE procuration_proxies_to_election_rounds');
        $this->addSql('DROP TABLE elections');
        $this->addSql('DROP TABLE election_rounds');
        $this->addSql('ALTER TABLE procuration_proxies ADD election_presidential_first_round TINYINT(1) NOT NULL, ADD election_presidential_second_round TINYINT(1) NOT NULL, ADD election_legislative_first_round TINYINT(1) NOT NULL, ADD election_legislative_second_round TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE procuration_requests ADD election_presidential_first_round TINYINT(1) NOT NULL, ADD election_presidential_second_round TINYINT(1) NOT NULL, ADD election_legislative_first_round TINYINT(1) NOT NULL, ADD election_legislative_second_round TINYINT(1) NOT NULL');
    }

    public function postDown(Schema $schema): void
    {
        foreach ($this->requestsToRounds as $r) {
            $this->updateLegacyRoundField(self::REQUESTS_TABLE, $this->roundIds[$r['election_round_id']], $r[self::REQUEST_JOIN_FIELD]);
        }
        foreach ($this->proposalsToRounds as $r) {
            $this->updateLegacyRoundField(self::PROXIES_TABLE, $this->roundIds[$r['election_round_id']], $r[self::PROXY_JOIN_FIELD]);
        }
    }

    private function selectLegacyRounds(string $table): array
    {
        return $this->connection->executeQuery(sprintf(
            'SELECT id, %s, %s, %s, %s FROM %s',
            self::PRESIDENTIAL_FIRST_ROUND,
            self::PRESIDENTIAL_SECOND_ROUND,
            self::LEGISLATIVE_FIRST_ROUND,
            self::LEGISLATIVE_SECOND_ROUND,
            $table
        ))->fetchAll();
    }

    private function insertElection(string $name, string $introduction): int
    {
        $this->connection->executeQuery(
            'INSERT INTO elections (`name`, `introduction`) VALUES (?, ?)',
            [$name, $introduction],
            [\PDO::PARAM_STR, \PDO::PARAM_STR]
        );

        return $this->connection->lastInsertId();
    }

    private function insertElectionRound(int $electionId, string $label, string $description, string $date): int
    {
        $this->connection->executeQuery(
            'INSERT INTO election_rounds (`label`, `description`, `election_id`, `date`) VALUES (?, ?, ?, ?)',
            [$label, $description, $electionId, $date],
            [\PDO::PARAM_STR, \PDO::PARAM_STR, \PDO::PARAM_INT, \PDO::PARAM_STR]
        );

        return $this->connection->lastInsertId();
    }

    private function linkNewDataToLegacy(string $table, array $data): void
    {
        foreach ($this->roundIds as $round => $id) {
            if ($data[$round]) {
                $this->insertProcurationRound($table, $id, $data['id']);
            }
        }
    }

    private function insertProcurationRound(string $table, int $roundId, int $id): void
    {
        $this->connection->executeQuery(
            sprintf('INSERT INTO %s_to_election_rounds (election_round_id, %s) VALUES (?, ?)', $table, self::JOIN_FIELDS[$table]),
            [$roundId, $id],
            [\PDO::PARAM_INT, \PDO::PARAM_INT]
        );
    }

    private function getRoundId(string $roundLabel): int
    {
        return $this->connection->executeQuery('SELECT `id` FROM election_rounds WHERE `label` = ?', [$roundLabel], [\PDO::PARAM_STR])->fetch()['id'];
    }

    private function selectJoinedRounds(string $table): array
    {
        return $this->connection->executeQuery(
            sprintf('SELECT * FROM %s_to_election_rounds WHERE `election_round_id` IN (?, ?, ?, ?)', $table),
            array_keys($this->roundIds),
            array_fill(0, 4, \PDO::PARAM_INT)
        )->fetchAll();
    }

    private function updateLegacyRoundField(string $table, string $field, int $id): void
    {
        $this->connection->executeQuery(
            sprintf('UPDATE %s SET %s = ? WHERE `id` = ?', $table, $field),
            [true, $id],
            [\PDO::PARAM_BOOL, \PDO::PARAM_INT]
        );
    }
}
