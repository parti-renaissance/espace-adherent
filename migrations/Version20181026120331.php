<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20181026120331 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE deputy_managed_users_message (id INT UNSIGNED AUTO_INCREMENT NOT NULL, district_id INT UNSIGNED DEFAULT NULL, adherent_id INT UNSIGNED DEFAULT NULL, subject VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, offset BIGINT NOT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_5AC419DDB08FA272 (district_id), INDEX IDX_5AC419DD25F06C53 (adherent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE deputy_managed_users_message ADD CONSTRAINT FK_5AC419DDB08FA272 FOREIGN KEY (district_id) REFERENCES districts (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE deputy_managed_users_message ADD CONSTRAINT FK_5AC419DD25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE deputy_managed_users_message');
    }
}
