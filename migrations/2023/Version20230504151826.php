<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230504151826 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE email_template_zone (
          email_template_id INT UNSIGNED NOT NULL,
          zone_id INT UNSIGNED NOT NULL,
          INDEX IDX_8712F9C2131A730F (email_template_id),
          INDEX IDX_8712F9C29F2C3FAB (zone_id),
          PRIMARY KEY(email_template_id, zone_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          email_template_zone
        ADD
          CONSTRAINT FK_8712F9C2131A730F FOREIGN KEY (email_template_id) REFERENCES email_templates (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          email_template_zone
        ADD
          CONSTRAINT FK_8712F9C29F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE email_templates DROP FOREIGN KEY FK_6023E2A5F675F31B');
        $this->addSql('DROP INDEX IDX_6023E2A5F675F31B ON email_templates');
        $this->addSql('ALTER TABLE
          email_templates
        ADD
          created_by_administrator_id INT DEFAULT NULL,
        ADD
          updated_by_administrator_id INT DEFAULT NULL,
        ADD
          updated_by_adherent_id INT UNSIGNED DEFAULT NULL,
        ADD
          scopes LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\',
        ADD
          json_content LONGTEXT DEFAULT NULL,
        CHANGE
          author_id created_by_adherent_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          email_templates
        ADD
          CONSTRAINT FK_6023E2A59DF5350C FOREIGN KEY (created_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          email_templates
        ADD
          CONSTRAINT FK_6023E2A5CF1918FF FOREIGN KEY (updated_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          email_templates
        ADD
          CONSTRAINT FK_6023E2A585C9D733 FOREIGN KEY (created_by_adherent_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          email_templates
        ADD
          CONSTRAINT FK_6023E2A5DF6CFDC9 FOREIGN KEY (updated_by_adherent_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('CREATE INDEX IDX_6023E2A59DF5350C ON email_templates (created_by_administrator_id)');
        $this->addSql('CREATE INDEX IDX_6023E2A5CF1918FF ON email_templates (updated_by_administrator_id)');
        $this->addSql('CREATE INDEX IDX_6023E2A585C9D733 ON email_templates (created_by_adherent_id)');
        $this->addSql('CREATE INDEX IDX_6023E2A5DF6CFDC9 ON email_templates (updated_by_adherent_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE email_template_zone DROP FOREIGN KEY FK_8712F9C2131A730F');
        $this->addSql('ALTER TABLE email_template_zone DROP FOREIGN KEY FK_8712F9C29F2C3FAB');
        $this->addSql('DROP TABLE email_template_zone');
        $this->addSql('ALTER TABLE email_templates DROP FOREIGN KEY FK_6023E2A59DF5350C');
        $this->addSql('ALTER TABLE email_templates DROP FOREIGN KEY FK_6023E2A5CF1918FF');
        $this->addSql('ALTER TABLE email_templates DROP FOREIGN KEY FK_6023E2A585C9D733');
        $this->addSql('ALTER TABLE email_templates DROP FOREIGN KEY FK_6023E2A5DF6CFDC9');
        $this->addSql('DROP INDEX IDX_6023E2A59DF5350C ON email_templates');
        $this->addSql('DROP INDEX IDX_6023E2A5CF1918FF ON email_templates');
        $this->addSql('DROP INDEX IDX_6023E2A585C9D733 ON email_templates');
        $this->addSql('DROP INDEX IDX_6023E2A5DF6CFDC9 ON email_templates');
        $this->addSql('ALTER TABLE
          email_templates
        ADD
          author_id INT UNSIGNED DEFAULT NULL,
        DROP
          created_by_administrator_id,
        DROP
          updated_by_administrator_id,
        DROP
          created_by_adherent_id,
        DROP
          updated_by_adherent_id,
        DROP
          scopes,
        DROP
          json_content');
        $this->addSql('ALTER TABLE
          email_templates
        ADD
          CONSTRAINT FK_6023E2A5F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_6023E2A5F675F31B ON email_templates (author_id)');
    }
}
