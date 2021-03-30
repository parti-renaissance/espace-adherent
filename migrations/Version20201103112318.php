<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201103112318 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE projection_managed_users ADD zone_id INT UNSIGNED DEFAULT NULL, ADD address VARCHAR(150) DEFAULT NULL');
        $this->addSql('ALTER TABLE projection_managed_users ADD CONSTRAINT FK_90A7D6569F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id)');
        $this->addSql('CREATE INDEX IDX_90A7D6569F2C3FAB ON projection_managed_users (zone_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE projection_managed_users DROP FOREIGN KEY FK_90A7D6569F2C3FAB');
        $this->addSql('DROP INDEX IDX_90A7D6569F2C3FAB ON projection_managed_users');
        $this->addSql('ALTER TABLE projection_managed_users DROP zone_id, DROP address');
    }
}
