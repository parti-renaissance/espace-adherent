<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220419193718 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE legislative_newsletter_subscription_zone (
          legislative_newsletter_subscription_id INT UNSIGNED NOT NULL,
          zone_id INT UNSIGNED NOT NULL,
          INDEX IDX_4E900BCF7F7EF992 (
            legislative_newsletter_subscription_id
          ),
          INDEX IDX_4E900BCF9F2C3FAB (zone_id),
          PRIMARY KEY(
            legislative_newsletter_subscription_id,
            zone_id
          )
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          legislative_newsletter_subscription_zone
        ADD
          CONSTRAINT FK_4E900BCF7F7EF992 FOREIGN KEY (
            legislative_newsletter_subscription_id
          ) REFERENCES legislative_newsletter_subscription (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          legislative_newsletter_subscription_zone
        ADD
          CONSTRAINT FK_4E900BCF9F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE legislative_newsletter_subscription DROP FOREIGN KEY FK_2672FB761972DC04');
        $this->addSql('DROP INDEX IDX_2672FB761972DC04 ON legislative_newsletter_subscription');
        $this->addSql('ALTER TABLE legislative_newsletter_subscription DROP from_zone_id');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2672FB76B08E074E ON legislative_newsletter_subscription (email_address)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE legislative_newsletter_subscription_zone');
        $this->addSql('DROP INDEX UNIQ_2672FB76B08E074E ON legislative_newsletter_subscription');
        $this->addSql('ALTER TABLE legislative_newsletter_subscription ADD from_zone_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          legislative_newsletter_subscription
        ADD
          CONSTRAINT FK_2672FB761972DC04 FOREIGN KEY (from_zone_id) REFERENCES geo_zone (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_2672FB761972DC04 ON legislative_newsletter_subscription (from_zone_id)');
    }
}
