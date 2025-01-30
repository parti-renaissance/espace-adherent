<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230727095418 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_message_filters DROP FOREIGN KEY FK_28CA9F9466E2221E');
        $this->addSql('ALTER TABLE events DROP FOREIGN KEY FK_5387574A66E2221E');
        $this->addSql('ALTER TABLE events DROP FOREIGN KEY FK_5387574AC2A46A23');
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA38828ED30');
        $this->addSql('ALTER TABLE cause DROP FOREIGN KEY FK_F0DA7FBF38C2B2DC');
        $this->addSql('ALTER TABLE cause DROP FOREIGN KEY FK_F0DA7FBFC2A46A23');
        $this->addSql('ALTER TABLE cause DROP FOREIGN KEY FK_F0DA7FBFF675F31B');
        $this->addSql('ALTER TABLE cause_follower DROP FOREIGN KEY FK_6F9A854425F06C53');
        $this->addSql('ALTER TABLE cause_follower DROP FOREIGN KEY FK_6F9A854466E2221E');
        $this->addSql('ALTER TABLE cause_follower DROP FOREIGN KEY FK_6F9A85449F2C3FAB');
        $this->addSql('ALTER TABLE cause_quick_action DROP FOREIGN KEY FK_DC1B329B66E2221E');
        $this->addSql('ALTER TABLE coalition_follower DROP FOREIGN KEY FK_DFF370E225F06C53');
        $this->addSql('ALTER TABLE coalition_follower DROP FOREIGN KEY FK_DFF370E2C2A46A23');
        $this->addSql('DROP TABLE cause');
        $this->addSql('DROP TABLE cause_follower');
        $this->addSql('DROP TABLE cause_quick_action');
        $this->addSql('DROP TABLE coalition');
        $this->addSql('DROP TABLE coalition_follower');
        $this->addSql('DROP TABLE coalition_moderator_role_association');
        $this->addSql('DROP INDEX IDX_28CA9F9466E2221E ON adherent_message_filters');
        $this->addSql('ALTER TABLE adherent_message_filters DROP cause_id');
        $this->addSql('DROP INDEX UNIQ_562C7DA38828ED30 ON adherents');
        $this->addSql('ALTER TABLE
          adherents
        DROP
          coalition_moderator_role_id,
        DROP
          coalition_subscription,
        DROP
          cause_subscription,
        DROP
          coalitions_cgu_accepted');
        $this->addSql('DROP INDEX IDX_5387574A66E2221E ON events');
        $this->addSql('DROP INDEX IDX_5387574AC2A46A23 ON events');
        $this->addSql('ALTER TABLE events DROP coalition_id, DROP cause_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE cause (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          coalition_id INT UNSIGNED NOT NULL,
          author_id INT UNSIGNED DEFAULT NULL,
          second_coalition_id INT UNSIGNED DEFAULT NULL,
          name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          description LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          image_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          canonical_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          slug VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          status VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          followers_count INT UNSIGNED NOT NULL,
          mailchimp_id INT DEFAULT NULL,
          UNIQUE INDEX UNIQ_F0DA7FBF5E237E06 (name),
          UNIQUE INDEX UNIQ_F0DA7FBFD17F50A6 (uuid),
          INDEX IDX_F0DA7FBF38C2B2DC (second_coalition_id),
          INDEX IDX_F0DA7FBFC2A46A23 (coalition_id),
          INDEX IDX_F0DA7FBFF675F31B (author_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE cause_follower (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          cause_id INT UNSIGNED NOT NULL,
          adherent_id INT UNSIGNED DEFAULT NULL,
          zone_id INT UNSIGNED DEFAULT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          first_name VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          email_address VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          cgu_accepted TINYINT(1) DEFAULT NULL,
          cause_subscription TINYINT(1) DEFAULT NULL,
          coalition_subscription TINYINT(1) DEFAULT NULL,
          UNIQUE INDEX UNIQ_6F9A8544D17F50A6 (uuid),
          UNIQUE INDEX cause_follower_unique (cause_id, adherent_id),
          INDEX IDX_6F9A854425F06C53 (adherent_id),
          INDEX IDX_6F9A854466E2221E (cause_id),
          INDEX IDX_6F9A85449F2C3FAB (zone_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE cause_quick_action (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          cause_id INT UNSIGNED NOT NULL,
          title VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          url VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          INDEX IDX_DC1B329B66E2221E (cause_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE coalition (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          description LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          enabled TINYINT(1) DEFAULT 1 NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          image_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          youtube_id VARCHAR(11) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          UNIQUE INDEX UNIQ_A7CD7AC75E237E06 (name),
          UNIQUE INDEX UNIQ_A7CD7AC7D17F50A6 (uuid),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE coalition_follower (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          coalition_id INT UNSIGNED NOT NULL,
          adherent_id INT UNSIGNED DEFAULT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_DFF370E2D17F50A6 (uuid),
          INDEX IDX_DFF370E225F06C53 (adherent_id),
          INDEX IDX_DFF370E2C2A46A23 (coalition_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE coalition_moderator_role_association (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('ALTER TABLE
          cause
        ADD
          CONSTRAINT FK_F0DA7FBF38C2B2DC FOREIGN KEY (second_coalition_id) REFERENCES coalition (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE
          cause
        ADD
          CONSTRAINT FK_F0DA7FBFC2A46A23 FOREIGN KEY (coalition_id) REFERENCES coalition (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE
          cause
        ADD
          CONSTRAINT FK_F0DA7FBFF675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON UPDATE NO ACTION ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          cause_follower
        ADD
          CONSTRAINT FK_6F9A854425F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON UPDATE NO ACTION ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          cause_follower
        ADD
          CONSTRAINT FK_6F9A854466E2221E FOREIGN KEY (cause_id) REFERENCES cause (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          cause_follower
        ADD
          CONSTRAINT FK_6F9A85449F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id) ON UPDATE NO ACTION ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          cause_quick_action
        ADD
          CONSTRAINT FK_DC1B329B66E2221E FOREIGN KEY (cause_id) REFERENCES cause (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          coalition_follower
        ADD
          CONSTRAINT FK_DFF370E225F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          coalition_follower
        ADD
          CONSTRAINT FK_DFF370E2C2A46A23 FOREIGN KEY (coalition_id) REFERENCES coalition (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE adherent_message_filters ADD cause_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          adherent_message_filters
        ADD
          CONSTRAINT FK_28CA9F9466E2221E FOREIGN KEY (cause_id) REFERENCES cause (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_28CA9F9466E2221E ON adherent_message_filters (cause_id)');
        $this->addSql('ALTER TABLE
          adherents
        ADD
          coalition_moderator_role_id INT UNSIGNED DEFAULT NULL,
        ADD
          coalition_subscription TINYINT(1) DEFAULT 0 NOT NULL,
        ADD
          cause_subscription TINYINT(1) DEFAULT 0 NOT NULL,
        ADD
          coalitions_cgu_accepted TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE
          adherents
        ADD
          CONSTRAINT FK_562C7DA38828ED30 FOREIGN KEY (coalition_moderator_role_id) REFERENCES coalition_moderator_role_association (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA38828ED30 ON adherents (coalition_moderator_role_id)');
        $this->addSql('ALTER TABLE
          events
        ADD
          coalition_id INT UNSIGNED DEFAULT NULL,
        ADD
          cause_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          events
        ADD
          CONSTRAINT FK_5387574A66E2221E FOREIGN KEY (cause_id) REFERENCES cause (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE
          events
        ADD
          CONSTRAINT FK_5387574AC2A46A23 FOREIGN KEY (coalition_id) REFERENCES coalition (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_5387574A66E2221E ON events (cause_id)');
        $this->addSql('CREATE INDEX IDX_5387574AC2A46A23 ON events (coalition_id)');
    }
}
