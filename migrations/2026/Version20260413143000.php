<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260413143000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE scope SET features = REPLACE(features, 'rentree', 'national_event') WHERE features LIKE '%rentree%'");
        $this->addSql("UPDATE my_team_member SET scope_features = REPLACE(scope_features, 'rentree', 'national_event') WHERE scope_features LIKE '%rentree%'");
        $this->addSql("UPDATE my_team_delegated_access SET scope_features = REPLACE(scope_features, 'rentree', 'national_event') WHERE scope_features LIKE '%rentree%'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("UPDATE scope SET features = REPLACE(features, 'national_event', 'rentree') WHERE features LIKE '%national_event%'");
        $this->addSql("UPDATE my_team_member SET scope_features = REPLACE(scope_features, 'national_event', 'rentree') WHERE scope_features LIKE '%national_event%'");
        $this->addSql("UPDATE my_team_delegated_access SET scope_features = REPLACE(scope_features, 'national_event', 'rentree') WHERE scope_features LIKE '%national_event%'");
    }
}
