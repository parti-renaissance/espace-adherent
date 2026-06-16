<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260616091659 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE app_alert ADD image_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE app_alert ADD CONSTRAINT FK_C12ECB0C3DA5256D FOREIGN KEY (image_id) REFERENCES uploadable_file (id) ON DELETE SET NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C12ECB0C3DA5256D ON app_alert (image_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE app_alert DROP FOREIGN KEY FK_C12ECB0C3DA5256D');
        $this->addSql('DROP INDEX UNIQ_C12ECB0C3DA5256D ON app_alert');
        $this->addSql('ALTER TABLE app_alert DROP image_id');
    }
}
