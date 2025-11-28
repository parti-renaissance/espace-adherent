<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240711165324 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE interactive_invitation_has_choices DROP FOREIGN KEY FK_31A811A2998666D1');
        $this->addSql('ALTER TABLE interactive_invitation_has_choices DROP FOREIGN KEY FK_31A811A2A35D7AF0');
        $this->addSql('ALTER TABLE ton_macron_friend_invitation_has_choices DROP FOREIGN KEY FK_BB3BCAEE998666D1');
        $this->addSql('ALTER TABLE ton_macron_friend_invitation_has_choices DROP FOREIGN KEY FK_BB3BCAEEA35D7AF0');
        $this->addSql('DROP TABLE interactive_choices');
        $this->addSql('DROP TABLE interactive_invitation_has_choices');
        $this->addSql('DROP TABLE interactive_invitations');
        $this->addSql('DROP TABLE ton_macron_choices');
        $this->addSql('DROP TABLE ton_macron_friend_invitation_has_choices');
        $this->addSql('DROP TABLE ton_macron_friend_invitations');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE interactive_choices (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          step SMALLINT UNSIGNED NOT NULL,
          content_key VARCHAR(30) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          label VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          content LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          TYPE VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          UNIQUE INDEX UNIQ_3C6695A73F7BFD5C (content_key),
          UNIQUE INDEX UNIQ_3C6695A7D17F50A6 (uuid),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE interactive_invitation_has_choices (
          invitation_id INT UNSIGNED NOT NULL,
          choice_id INT UNSIGNED NOT NULL,
          INDEX IDX_31A811A2998666D1 (choice_id),
          INDEX IDX_31A811A2A35D7AF0 (invitation_id),
          PRIMARY KEY(invitation_id, choice_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE interactive_invitations (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          friend_age SMALLINT UNSIGNED NOT NULL,
          friend_gender VARCHAR(6) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          friend_position VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          author_first_name VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          author_last_name VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          author_email_address VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          mail_subject VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          mail_body LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          created_at DATETIME NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          TYPE VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          UNIQUE INDEX UNIQ_45258689D17F50A6 (uuid),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE ton_macron_choices (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          step SMALLINT UNSIGNED NOT NULL,
          content_key VARCHAR(30) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          label VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          content LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          UNIQUE INDEX UNIQ_6247B0DE3F7BFD5C (content_key),
          UNIQUE INDEX UNIQ_6247B0DED17F50A6 (uuid),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE ton_macron_friend_invitation_has_choices (
          invitation_id INT UNSIGNED NOT NULL,
          choice_id INT UNSIGNED NOT NULL,
          INDEX IDX_BB3BCAEE998666D1 (choice_id),
          INDEX IDX_BB3BCAEEA35D7AF0 (invitation_id),
          PRIMARY KEY(invitation_id, choice_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE ton_macron_friend_invitations (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          friend_first_name VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          friend_age SMALLINT UNSIGNED NOT NULL,
          friend_gender VARCHAR(6) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          friend_position VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          friend_email_address VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          author_first_name VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          author_last_name VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          author_email_address VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          mail_subject VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          mail_body LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          created_at DATETIME NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          UNIQUE INDEX UNIQ_78714946D17F50A6 (uuid),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('ALTER TABLE
          interactive_invitation_has_choices
        ADD
          CONSTRAINT FK_31A811A2998666D1 FOREIGN KEY (choice_id) REFERENCES interactive_choices (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE
          interactive_invitation_has_choices
        ADD
          CONSTRAINT FK_31A811A2A35D7AF0 FOREIGN KEY (invitation_id) REFERENCES interactive_invitations (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE
          ton_macron_friend_invitation_has_choices
        ADD
          CONSTRAINT FK_BB3BCAEE998666D1 FOREIGN KEY (choice_id) REFERENCES ton_macron_choices (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE
          ton_macron_friend_invitation_has_choices
        ADD
          CONSTRAINT FK_BB3BCAEEA35D7AF0 FOREIGN KEY (invitation_id) REFERENCES ton_macron_friend_invitations (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
