<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250917072949 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE app_hit DROP FOREIGN KEY FK_74A09586372447A3');
        $this->addSql('ALTER TABLE
          app_hit
        ADD
          CONSTRAINT FK_74A09586372447A3 FOREIGN KEY (app_session_id) REFERENCES app_session (id) ON DELETE
        SET
          NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE app_hit DROP FOREIGN KEY FK_74A09586372447A3');
        $this->addSql('ALTER TABLE
          app_hit
        ADD
          CONSTRAINT FK_74A09586372447A3 FOREIGN KEY (app_session_id) REFERENCES app_session (id) ON
        UPDATE
          NO ACTION ON DELETE NO ACTION');
    }
}
