<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221202161505 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX IDX_77220D7DE7927C74 ON local_election_candidacy (email)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_77220D7DE7927C74 ON local_election_candidacy');
    }
}
