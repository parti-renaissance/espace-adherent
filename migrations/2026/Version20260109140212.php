<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260109140212 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE national_event ADD favicon_image_id INT UNSIGNED DEFAULT NULL');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  national_event
                ADD
                  CONSTRAINT FK_AD03766440C7851E FOREIGN KEY (favicon_image_id) REFERENCES uploadable_file (id) ON DELETE
                SET
                  NULL
            SQL);
        $this->addSql('CREATE UNIQUE INDEX UNIQ_AD03766440C7851E ON national_event (favicon_image_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE national_event DROP FOREIGN KEY FK_AD03766440C7851E');
        $this->addSql('DROP INDEX UNIQ_AD03766440C7851E ON national_event');
        $this->addSql('ALTER TABLE national_event DROP favicon_image_id');
    }
}
