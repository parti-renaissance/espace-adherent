<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250402145616 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE national_event ADD logo_image_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          national_event
        ADD
          CONSTRAINT FK_AD0376646D947EBB FOREIGN KEY (logo_image_id) REFERENCES uploadable_file (id) ON DELETE
        SET
          NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_AD0376646D947EBB ON national_event (logo_image_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE national_event DROP FOREIGN KEY FK_AD0376646D947EBB');
        $this->addSql('DROP INDEX UNIQ_AD0376646D947EBB ON national_event');
        $this->addSql('ALTER TABLE national_event DROP logo_image_id');
    }
}
