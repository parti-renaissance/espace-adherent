<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20171117175255 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE events DROP FOREIGN KEY FK_5387574AFE54D947');
        $this->addSql('ALTER TABLE group_feed_items DROP FOREIGN KEY FK_C35CDB43FE54D947');
        $this->addSql('ALTER TABLE events DROP FOREIGN KEY FK_5387574ABE3E9D45');
        $this->addSql('CREATE TABLE citizen_action_categories (id INT UNSIGNED AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, status VARCHAR(10) DEFAULT \'ENABLED\' NOT NULL, UNIQUE INDEX citizen_action_category_name_unique (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE citizen_projects (id INT UNSIGNED AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, canonical_name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, status VARCHAR(20) NOT NULL, approved_at DATETIME DEFAULT NULL, refused_at DATETIME DEFAULT NULL, created_by CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', phone VARCHAR(35) DEFAULT NULL COMMENT \'(DC2Type:phone_number)\', members_counts SMALLINT UNSIGNED NOT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, address_address VARCHAR(150) DEFAULT NULL, address_postal_code VARCHAR(15) DEFAULT NULL, address_city_insee VARCHAR(15) DEFAULT NULL, address_city_name VARCHAR(255) DEFAULT NULL, address_country VARCHAR(2) DEFAULT NULL, address_latitude FLOAT (10,6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\', address_longitude FLOAT (10,6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\', INDEX citizen_project_status_idx (status), UNIQUE INDEX citizen_project_uuid_unique (uuid), UNIQUE INDEX citizen_project_canonical_name_unique (canonical_name), UNIQUE INDEX citizen_project_slug_unique (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE citizen_project_feed_items (id INT UNSIGNED AUTO_INCREMENT NOT NULL, citizen_project_id INT UNSIGNED DEFAULT NULL, author_id INT UNSIGNED DEFAULT NULL, event_id INT UNSIGNED DEFAULT NULL, item_type VARCHAR(18) NOT NULL, content LONGTEXT DEFAULT NULL, published TINYINT(1) DEFAULT \'1\' NOT NULL, created_at DATETIME NOT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_148F04E2B3584533 (citizen_project_id), INDEX IDX_148F04E2F675F31B (author_id), INDEX IDX_148F04E271F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE citizen_project_memberships (id INT UNSIGNED AUTO_INCREMENT NOT NULL, adherent_id INT UNSIGNED DEFAULT NULL, citizen_project_uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', privilege VARCHAR(15) NOT NULL, joined_at DATETIME NOT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_2E4181625F06C53 (adherent_id), INDEX citizen_project_memberships_role_idx (privilege), UNIQUE INDEX adherent_has_joined_citizen_project (adherent_id, citizen_project_uuid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE citizen_project_feed_items ADD CONSTRAINT FK_148F04E2B3584533 FOREIGN KEY (citizen_project_id) REFERENCES citizen_projects (id)');
        $this->addSql('ALTER TABLE citizen_project_feed_items ADD CONSTRAINT FK_148F04E2F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id)');
        $this->addSql('ALTER TABLE citizen_project_feed_items ADD CONSTRAINT FK_148F04E271F7E88B FOREIGN KEY (event_id) REFERENCES events (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE citizen_project_memberships ADD CONSTRAINT FK_2E4181625F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id)');
        $this->addSql('DROP TABLE group_feed_items');
        $this->addSql('DROP TABLE group_memberships');
        $this->addSql('DROP TABLE groups');
        $this->addSql('DROP TABLE mooc_event_categories');
        $this->addSql('DROP INDEX IDX_5387574AFE54D947 ON events');
        $this->addSql('DROP INDEX IDX_5387574ABE3E9D45 ON events');
        $this->addSql('ALTER TABLE events ADD citizen_project_id INT UNSIGNED DEFAULT NULL, ADD citizen_action_category_id INT UNSIGNED DEFAULT NULL, DROP group_id, DROP mooc_event_category_id');
        $this->addSql('ALTER TABLE events ADD CONSTRAINT FK_5387574AB3584533 FOREIGN KEY (citizen_project_id) REFERENCES citizen_projects (id)');
        $this->addSql('ALTER TABLE events ADD CONSTRAINT FK_5387574A712CD107 FOREIGN KEY (citizen_action_category_id) REFERENCES citizen_action_categories (id)');
        $this->addSql('CREATE INDEX IDX_5387574AB3584533 ON events (citizen_project_id)');
        $this->addSql('CREATE INDEX IDX_5387574A712CD107 ON events (citizen_action_category_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE events DROP FOREIGN KEY FK_5387574A712CD107');
        $this->addSql('ALTER TABLE events DROP FOREIGN KEY FK_5387574AB3584533');
        $this->addSql('ALTER TABLE citizen_project_feed_items DROP FOREIGN KEY FK_148F04E2B3584533');
        $this->addSql('CREATE TABLE group_feed_items (id INT UNSIGNED AUTO_INCREMENT NOT NULL, group_id INT UNSIGNED DEFAULT NULL, author_id INT UNSIGNED DEFAULT NULL, event_id INT UNSIGNED DEFAULT NULL, item_type VARCHAR(18) NOT NULL COLLATE utf8_unicode_ci, content LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci, published TINYINT(1) DEFAULT \'1\' NOT NULL, created_at DATETIME NOT NULL, uuid CHAR(36) NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:uuid)\', INDEX IDX_C35CDB43FE54D947 (group_id), INDEX IDX_C35CDB43F675F31B (author_id), INDEX IDX_C35CDB4371F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE group_memberships (id INT UNSIGNED AUTO_INCREMENT NOT NULL, adherent_id INT UNSIGNED DEFAULT NULL, group_uuid CHAR(36) NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:uuid)\', privilege VARCHAR(15) NOT NULL COLLATE utf8_unicode_ci, joined_at DATETIME NOT NULL, uuid CHAR(36) NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:uuid)\', UNIQUE INDEX adherent_has_joined_group (adherent_id, group_uuid), INDEX IDX_A3E258B725F06C53 (adherent_id), INDEX groups_memberships_role_idx (privilege), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE groups (id INT UNSIGNED AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, canonical_name VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, slug VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, description LONGTEXT NOT NULL COLLATE utf8_unicode_ci, status VARCHAR(20) NOT NULL COLLATE utf8_unicode_ci, approved_at DATETIME DEFAULT NULL, refused_at DATETIME DEFAULT NULL, created_by CHAR(36) DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:uuid)\', phone VARCHAR(35) DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:phone_number)\', members_counts SMALLINT UNSIGNED NOT NULL, uuid CHAR(36) NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:uuid)\', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, address_address VARCHAR(150) DEFAULT NULL COLLATE utf8_unicode_ci, address_postal_code VARCHAR(15) DEFAULT NULL COLLATE utf8_unicode_ci, address_city_insee VARCHAR(15) DEFAULT NULL COLLATE utf8_unicode_ci, address_city_name VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, address_country VARCHAR(2) DEFAULT NULL COLLATE utf8_unicode_ci, address_latitude FLOAT (10,6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\', address_longitude FLOAT (10,6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\', UNIQUE INDEX group_uuid_unique (uuid), UNIQUE INDEX group_canonical_name_unique (canonical_name), UNIQUE INDEX group_slug_unique (slug), INDEX group_status_idx (status), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mooc_event_categories (id INT UNSIGNED AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL COLLATE utf8_unicode_ci, status VARCHAR(10) DEFAULT \'ENABLED\' NOT NULL COLLATE utf8_unicode_ci, UNIQUE INDEX mooc_event_category_name_unique (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE group_feed_items ADD CONSTRAINT FK_C35CDB4371F7E88B FOREIGN KEY (event_id) REFERENCES events (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE group_feed_items ADD CONSTRAINT FK_C35CDB43F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id)');
        $this->addSql('ALTER TABLE group_feed_items ADD CONSTRAINT FK_C35CDB43FE54D947 FOREIGN KEY (group_id) REFERENCES groups (id)');
        $this->addSql('ALTER TABLE group_memberships ADD CONSTRAINT FK_A3E258B725F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id)');
        $this->addSql('DROP TABLE citizen_action_categories');
        $this->addSql('DROP TABLE citizen_projects');
        $this->addSql('DROP TABLE citizen_project_feed_items');
        $this->addSql('DROP TABLE citizen_project_memberships');
        $this->addSql('DROP INDEX IDX_5387574AB3584533 ON events');
        $this->addSql('DROP INDEX IDX_5387574A712CD107 ON events');
        $this->addSql('ALTER TABLE events ADD group_id INT UNSIGNED DEFAULT NULL, ADD mooc_event_category_id INT UNSIGNED DEFAULT NULL, DROP citizen_project_id, DROP citizen_action_category_id');
        $this->addSql('ALTER TABLE events ADD CONSTRAINT FK_5387574ABE3E9D45 FOREIGN KEY (mooc_event_category_id) REFERENCES mooc_event_categories (id)');
        $this->addSql('ALTER TABLE events ADD CONSTRAINT FK_5387574AFE54D947 FOREIGN KEY (group_id) REFERENCES groups (id)');
        $this->addSql('CREATE INDEX IDX_5387574AFE54D947 ON events (group_id)');
        $this->addSql('CREATE INDEX IDX_5387574ABE3E9D45 ON events (mooc_event_category_id)');
    }
}
