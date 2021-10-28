<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211022141258 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE jecoute_data_answer CHANGE text_field text_field LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE
          jemarche_data_survey
        CHANGE
          first_name first_name LONGTEXT DEFAULT NULL,
        CHANGE
          last_name last_name LONGTEXT DEFAULT NULL,
        CHANGE
          email_address email_address LONGTEXT DEFAULT NULL,
        CHANGE
          postal_code postal_code LONGTEXT DEFAULT NULL,
        CHANGE
          gender_other gender_other LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          jecoute_data_answer
        CHANGE
          text_field text_field VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE
          jemarche_data_survey
        CHANGE
          first_name first_name VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
        CHANGE
          last_name last_name VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
        CHANGE
          email_address email_address VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
        CHANGE
          postal_code postal_code VARCHAR(5) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
        CHANGE
          gender_other gender_other VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
