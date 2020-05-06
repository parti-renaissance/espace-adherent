<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200504123507 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE elected_representative_zone_referent_tag (elected_representative_zone_id INT NOT NULL, referent_tag_id INT UNSIGNED NOT NULL, INDEX IDX_D2B7A8C5BE31A103 (elected_representative_zone_id), INDEX IDX_D2B7A8C59C262DB3 (referent_tag_id), PRIMARY KEY(elected_representative_zone_id, referent_tag_id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE elected_representative_zone_referent_tag ADD CONSTRAINT FK_D2B7A8C5BE31A103 FOREIGN KEY (elected_representative_zone_id) REFERENCES elected_representative_zone (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE elected_representative_zone_referent_tag ADD CONSTRAINT FK_D2B7A8C59C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE elected_representative_zone_referent_tag');
    }
}
