<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250416162120 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_app_session DROP FOREIGN KEY FK_BC869A7725F06C53');
        $this->addSql('ALTER TABLE adherent_app_session DROP FOREIGN KEY FK_BC869A77372447A3');
        $this->addSql('DROP TABLE adherent_app_session');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE adherent_app_session (
          adherent_id INT UNSIGNED NOT NULL,
          app_session_id INT UNSIGNED NOT NULL,
          INDEX IDX_BC869A7725F06C53 (adherent_id),
          INDEX IDX_BC869A77372447A3 (app_session_id),
          PRIMARY KEY(adherent_id, app_session_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('ALTER TABLE
          adherent_app_session
        ADD
          CONSTRAINT FK_BC869A7725F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON
        UPDATE
          NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          adherent_app_session
        ADD
          CONSTRAINT FK_BC869A77372447A3 FOREIGN KEY (app_session_id) REFERENCES app_session (id) ON
        UPDATE
          NO ACTION ON DELETE CASCADE');
    }
}
