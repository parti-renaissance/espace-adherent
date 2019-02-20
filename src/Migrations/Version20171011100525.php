<?php

namespace Migrations;

use AppBundle\DataFixtures\ORM\LoadBoardMemberRoleData;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20171011100525 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('CREATE TABLE board_member (id INT AUTO_INCREMENT NOT NULL, adherent_id INT UNSIGNED DEFAULT NULL, area VARCHAR(50) NOT NULL, UNIQUE INDEX UNIQ_DCFABEDF25F06C53 (adherent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE board_member_roles (board_member_id INT NOT NULL, role_id INT UNSIGNED NOT NULL, INDEX IDX_1DD1E043C7BA2FD5 (board_member_id), INDEX IDX_1DD1E043D60322AC (role_id), PRIMARY KEY(board_member_id, role_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE roles (id INT UNSIGNED AUTO_INCREMENT NOT NULL, code VARCHAR(20) NOT NULL, name VARCHAR(100) NOT NULL, UNIQUE INDEX board_member_role_code_unique (code), UNIQUE INDEX board_member_role_name_unique (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE board_member ADD CONSTRAINT FK_DCFABEDF25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE board_member_roles ADD CONSTRAINT FK_1DD1E043C7BA2FD5 FOREIGN KEY (board_member_id) REFERENCES board_member (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE board_member_roles ADD CONSTRAINT FK_1DD1E043D60322AC FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE');
    }

    public function postUp(Schema $schema)
    {
        foreach (LoadBoardMemberRoleData::BOARD_MEMBER_ROLES as $code => $name) {
            $this->connection->insert('roles', ['code' => $code, 'name' => $name]);
        }
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE board_member_roles DROP FOREIGN KEY FK_1DD1E043C7BA2FD5');
        $this->addSql('ALTER TABLE board_member_roles DROP FOREIGN KEY FK_1DD1E043D60322AC');
        $this->addSql('DROP TABLE board_member');
        $this->addSql('DROP TABLE board_member_roles');
        $this->addSql('DROP TABLE roles');
    }
}
