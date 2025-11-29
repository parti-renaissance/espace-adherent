<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240906131000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE board_member DROP FOREIGN KEY FK_DCFABEDF25F06C53');
        $this->addSql('ALTER TABLE board_member_roles DROP FOREIGN KEY FK_1DD1E043C7BA2FD5');
        $this->addSql('ALTER TABLE board_member_roles DROP FOREIGN KEY FK_1DD1E043D60322AC');
        $this->addSql('ALTER TABLE saved_board_members DROP FOREIGN KEY FK_32865A324821D202');
        $this->addSql('ALTER TABLE saved_board_members DROP FOREIGN KEY FK_32865A32FDCCD727');
        $this->addSql('DROP TABLE board_member');
        $this->addSql('DROP TABLE board_member_roles');
        $this->addSql('DROP TABLE roles');
        $this->addSql('DROP TABLE saved_board_members');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE board_member (
          id INT AUTO_INCREMENT NOT NULL,
          adherent_id INT UNSIGNED DEFAULT NULL,
          area VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          UNIQUE INDEX UNIQ_DCFABEDF25F06C53 (adherent_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE board_member_roles (
          board_member_id INT NOT NULL,
          role_id INT UNSIGNED NOT NULL,
          INDEX IDX_1DD1E043C7BA2FD5 (board_member_id),
          INDEX IDX_1DD1E043D60322AC (role_id),
          PRIMARY KEY(board_member_id, role_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE roles (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          code VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          name VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          UNIQUE INDEX UNIQ_B63E2EC75E237E06 (name),
          UNIQUE INDEX UNIQ_B63E2EC777153098 (code),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE saved_board_members (
          board_member_owner_id INT NOT NULL,
          board_member_saved_id INT NOT NULL,
          INDEX IDX_32865A324821D202 (board_member_saved_id),
          INDEX IDX_32865A32FDCCD727 (board_member_owner_id),
          PRIMARY KEY(
            board_member_owner_id, board_member_saved_id
          )
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('ALTER TABLE
          board_member
        ADD
          CONSTRAINT FK_DCFABEDF25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          board_member_roles
        ADD
          CONSTRAINT FK_1DD1E043C7BA2FD5 FOREIGN KEY (board_member_id) REFERENCES board_member (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          board_member_roles
        ADD
          CONSTRAINT FK_1DD1E043D60322AC FOREIGN KEY (role_id) REFERENCES roles (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          saved_board_members
        ADD
          CONSTRAINT FK_32865A324821D202 FOREIGN KEY (board_member_saved_id) REFERENCES board_member (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE
          saved_board_members
        ADD
          CONSTRAINT FK_32865A32FDCCD727 FOREIGN KEY (board_member_owner_id) REFERENCES board_member (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
