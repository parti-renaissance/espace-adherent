<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250414131242 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE adherent_app_session (
          adherent_id INT UNSIGNED NOT NULL,
          app_session_id INT UNSIGNED NOT NULL,
          INDEX IDX_BC869A7725F06C53 (adherent_id),
          INDEX IDX_BC869A77372447A3 (app_session_id),
          PRIMARY KEY(adherent_id, app_session_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE app_session (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          adherent_id INT UNSIGNED NOT NULL,
          client_id INT UNSIGNED DEFAULT NULL,
          status VARCHAR(255) NOT NULL,
          last_activity_date DATETIME DEFAULT NULL,
          user_agent VARCHAR(255) DEFAULT NULL,
          app_version VARCHAR(255) DEFAULT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_3D195599D17F50A6 (uuid),
          INDEX IDX_3D19559925F06C53 (adherent_id),
          INDEX IDX_3D19559919EB6921 (client_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          adherent_app_session
        ADD
          CONSTRAINT FK_BC869A7725F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          adherent_app_session
        ADD
          CONSTRAINT FK_BC869A77372447A3 FOREIGN KEY (app_session_id) REFERENCES app_session (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          app_session
        ADD
          CONSTRAINT FK_3D19559925F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          app_session
        ADD
          CONSTRAINT FK_3D19559919EB6921 FOREIGN KEY (client_id) REFERENCES oauth_clients (id)');
        $this->addSql('ALTER TABLE oauth_access_tokens ADD app_session_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          oauth_access_tokens
        ADD
          CONSTRAINT FK_CA42527C372447A3 FOREIGN KEY (app_session_id) REFERENCES app_session (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_CA42527C372447A3 ON oauth_access_tokens (app_session_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE oauth_access_tokens DROP FOREIGN KEY FK_CA42527C372447A3');
        $this->addSql('ALTER TABLE adherent_app_session DROP FOREIGN KEY FK_BC869A7725F06C53');
        $this->addSql('ALTER TABLE adherent_app_session DROP FOREIGN KEY FK_BC869A77372447A3');
        $this->addSql('ALTER TABLE app_session DROP FOREIGN KEY FK_3D19559925F06C53');
        $this->addSql('ALTER TABLE app_session DROP FOREIGN KEY FK_3D19559919EB6921');
        $this->addSql('DROP TABLE adherent_app_session');
        $this->addSql('DROP TABLE app_session');
        $this->addSql('DROP INDEX IDX_CA42527C372447A3 ON oauth_access_tokens');
        $this->addSql('ALTER TABLE oauth_access_tokens DROP app_session_id');
    }
}
