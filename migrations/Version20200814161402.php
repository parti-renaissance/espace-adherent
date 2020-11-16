<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200814161402 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_segment CHANGE mailchimp_id mailchimp_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE territorial_council ADD mailchimp_id INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          adherent_segment CHANGE mailchimp_id mailchimp_id VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE territorial_council DROP mailchimp_id');
    }
}
