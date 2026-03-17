<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260317143810 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX IDX_421C13B98CDE5729C9AA420C ON my_team_delegated_access (type, role_code)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_421C13B98CDE5729C9AA420C ON my_team_delegated_access');
    }
}
