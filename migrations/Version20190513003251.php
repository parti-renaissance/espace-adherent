<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190513003251 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          adherents 
        ADD 
          referent_team_member_id INT DEFAULT NULL, 
        ADD 
          board_member_id INT DEFAULT NULL, 
        ADD 
          managed_district_id INT UNSIGNED DEFAULT NULL');

        $this->addSql(
            'UPDATE adherents AS a
            INNER JOIN referent_team_member AS m ON m.member_id = a.id
            SET a.referent_team_member_id = m.id'
        );

        $this->addSql(
            'UPDATE adherents AS a
            INNER JOIN board_member AS m ON m.adherent_id = a.id
            SET a.board_member_id = m.id'
        );

        $this->addSql(
            'UPDATE adherents AS a
            INNER JOIN districts AS d ON d.adherent_id = a.id
            SET a.managed_district_id = d.id'
        );

        $this->addSql('ALTER TABLE districts DROP FOREIGN KEY FK_68E318DC25F06C53');
        $this->addSql('DROP INDEX UNIQ_68E318DC25F06C53 ON districts');
        $this->addSql('ALTER TABLE districts DROP adherent_id');
        $this->addSql('ALTER TABLE board_member DROP FOREIGN KEY FK_DCFABEDF25F06C53');
        $this->addSql('DROP INDEX UNIQ_DCFABEDF25F06C53 ON board_member');
        $this->addSql('ALTER TABLE board_member DROP adherent_id');

        $this->addSql('ALTER TABLE 
          adherents 
        ADD 
          CONSTRAINT FK_562C7DA3FE4CA267 FOREIGN KEY (referent_team_member_id) REFERENCES referent_team_member (id)');
        $this->addSql('ALTER TABLE 
          adherents 
        ADD 
          CONSTRAINT FK_562C7DA3C7BA2FD5 FOREIGN KEY (board_member_id) REFERENCES board_member (id)');
        $this->addSql('ALTER TABLE 
          adherents 
        ADD 
          CONSTRAINT FK_562C7DA3A132C3C5 FOREIGN KEY (managed_district_id) REFERENCES districts (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA3FE4CA267 ON adherents (referent_team_member_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA3C7BA2FD5 ON adherents (board_member_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA3A132C3C5 ON adherents (managed_district_id)');
        $this->addSql('ALTER TABLE referent_team_member DROP FOREIGN KEY FK_6C006717597D3FE');
        $this->addSql('DROP INDEX UNIQ_6C006717597D3FE ON referent_team_member');
        $this->addSql('ALTER TABLE referent_team_member DROP member_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA3FE4CA267');
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA3C7BA2FD5');
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA3A132C3C5');
        $this->addSql('DROP INDEX UNIQ_562C7DA3FE4CA267 ON adherents');
        $this->addSql('DROP INDEX UNIQ_562C7DA3C7BA2FD5 ON adherents');
        $this->addSql('DROP INDEX UNIQ_562C7DA3A132C3C5 ON adherents');
        $this->addSql('ALTER TABLE 
          adherents 
        DROP 
          referent_team_member_id, 
        DROP 
          board_member_id, 
        DROP 
          managed_district_id');
        $this->addSql('ALTER TABLE board_member ADD adherent_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          board_member 
        ADD 
          CONSTRAINT FK_DCFABEDF25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_DCFABEDF25F06C53 ON board_member (adherent_id)');
        $this->addSql('ALTER TABLE districts ADD adherent_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          districts 
        ADD 
          CONSTRAINT FK_68E318DC25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_68E318DC25F06C53 ON districts (adherent_id)');
        $this->addSql('ALTER TABLE referent_team_member ADD member_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE 
          referent_team_member 
        ADD 
          CONSTRAINT FK_6C006717597D3FE FOREIGN KEY (member_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6C006717597D3FE ON referent_team_member (member_id)');
    }
}
