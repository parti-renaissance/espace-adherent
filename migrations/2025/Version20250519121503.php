<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250519121503 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                CREATE INDEX IDX_9C0C3D608CDE5729 ON adherent_mandate (type)
            SQL);
        $this->addSql(<<<'SQL'
                CREATE INDEX IDX_3D1955997B00651C ON app_session (status)
            SQL);
        $this->addSql(<<<'SQL'
                CREATE INDEX IDX_3D1955992C4E7C0B ON app_session (app_system)
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                DROP INDEX IDX_9C0C3D608CDE5729 ON adherent_mandate
            SQL);
        $this->addSql(<<<'SQL'
                DROP INDEX IDX_3D1955997B00651C ON app_session
            SQL);
        $this->addSql(<<<'SQL'
                DROP INDEX IDX_3D1955992C4E7C0B ON app_session
            SQL);
    }
}
