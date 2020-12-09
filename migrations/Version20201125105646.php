<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20201125105646 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE territorial_council_zone (territorial_council_id INT UNSIGNED NOT NULL, zone_id INT UNSIGNED NOT NULL, INDEX IDX_9467B41EAAA61A99 (territorial_council_id), INDEX IDX_9467B41E9F2C3FAB (zone_id), PRIMARY KEY(territorial_council_id, zone_id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE territorial_council_zone ADD CONSTRAINT FK_9467B41EAAA61A99 FOREIGN KEY (territorial_council_id) REFERENCES territorial_council (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE territorial_council_zone ADD CONSTRAINT FK_9467B41E9F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE territorial_council_zone');
    }
}
