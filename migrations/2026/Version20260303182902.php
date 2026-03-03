<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260303182902 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                CREATE TABLE sms_opt_out (
                  id INT UNSIGNED AUTO_INCREMENT NOT NULL,
                  phone VARCHAR(35) NOT NULL,
                  source VARCHAR(255) NOT NULL,
                  created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                  cancelled_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                  INDEX IDX_C23DC819444F97DD (phone),
                  PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
            SQL);
        $this->addSql('ALTER TABLE adherents ADD mailchimp_contact_id VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE sms_opt_out');
        $this->addSql('ALTER TABLE adherents DROP mailchimp_contact_id');
    }
}
