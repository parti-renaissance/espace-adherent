<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231005220834 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_adherent_tag DROP FOREIGN KEY FK_DD297F8225F06C53');
        $this->addSql('ALTER TABLE adherent_adherent_tag DROP FOREIGN KEY FK_DD297F82AED03543');
        $this->addSql('DROP TABLE adherent_adherent_tag');
        $this->addSql('DROP TABLE adherent_tags');
        $this->addSql('ALTER TABLE
          adherents
        DROP
          local_host_emails_subscription,
        DROP
          com_mobile,
        DROP
          emails_subscriptions');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE adherent_adherent_tag (
          adherent_id INT UNSIGNED NOT NULL,
          adherent_tag_id INT UNSIGNED NOT NULL,
          INDEX IDX_DD297F8225F06C53 (adherent_id),
          INDEX IDX_DD297F82AED03543 (adherent_tag_id),
          PRIMARY KEY(adherent_id, adherent_tag_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE adherent_tags (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          name VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          UNIQUE INDEX UNIQ_D34384A45E237E06 (name),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('ALTER TABLE
          adherent_adherent_tag
        ADD
          CONSTRAINT FK_DD297F8225F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          adherent_adherent_tag
        ADD
          CONSTRAINT FK_DD297F82AED03543 FOREIGN KEY (adherent_tag_id) REFERENCES adherent_tags (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          adherents
        ADD
          local_host_emails_subscription TINYINT(1) DEFAULT 0 NOT NULL,
        ADD
          com_mobile TINYINT(1) DEFAULT NULL,
        ADD
          emails_subscriptions LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\'');
    }
}
