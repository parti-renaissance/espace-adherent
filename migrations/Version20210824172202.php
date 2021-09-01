<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210824172202 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE jecoute_data_survey DROP FOREIGN KEY FK_6579E8E794A4C7D4');
        $this->addSql('DROP INDEX IDX_6579E8E794A4C7D4 ON jecoute_data_survey');
        $this->addSql('ALTER TABLE
          jecoute_data_survey
        DROP
          device_id,
        DROP
          first_name,
        DROP
          last_name,
        DROP
          email_address,
        DROP
          agreed_to_stay_in_contact,
        DROP
          agreed_to_contact_for_join,
        DROP
          postal_code,
        DROP
          age_range,
        DROP
          gender,
        DROP
          gender_other,
        DROP
          agreed_to_treat_personal_data,
        DROP
          profession,
        DROP
          latitude,
        DROP
          longitude');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          jecoute_data_survey
        ADD
          device_id INT UNSIGNED DEFAULT NULL,
        ADD
          first_name VARCHAR(50) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`,
        ADD
          last_name VARCHAR(50) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`,
        ADD
          email_address VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`,
        ADD
          agreed_to_stay_in_contact TINYINT(1) NOT NULL,
        ADD
          agreed_to_contact_for_join TINYINT(1) NOT NULL,
        ADD
          postal_code VARCHAR(5) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`,
        ADD
          age_range VARCHAR(15) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`,
        ADD
          gender VARCHAR(15) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`,
        ADD
          gender_other VARCHAR(50) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`,
        ADD
          agreed_to_treat_personal_data TINYINT(1) NOT NULL,
        ADD
          profession VARCHAR(30) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`,
        ADD
          latitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
        ADD
          longitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\'');
        $this->addSql('ALTER TABLE
          jecoute_data_survey
        ADD
          CONSTRAINT FK_6579E8E794A4C7D4 FOREIGN KEY (device_id) REFERENCES devices (id) ON DELETE
        SET
          NULL');
        $this->addSql('CREATE INDEX IDX_6579E8E794A4C7D4 ON jecoute_data_survey (device_id)');
    }
}
