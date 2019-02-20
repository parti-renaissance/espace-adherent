<?php

namespace Migrations;

use AppBundle\Entity\Reporting\CommitteeMembershipAction;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20180523160911 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(
            <<<'SQL'
CREATE TABLE committees_membership_histories (
    id INT UNSIGNED AUTO_INCREMENT NOT NULL,
    committee_id INT UNSIGNED DEFAULT NULL,
    adherent_uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)',
    action VARCHAR(10) NOT NULL,
    privilege VARCHAR(10) NOT NULL,
    date DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
    INDEX IDX_4BBAE2C7ED1A100B (committee_id),
    INDEX committees_membership_histories_adherent_uuid_idx (adherent_uuid),
    INDEX committees_membership_histories_action_idx (action),
    INDEX committees_membership_histories_date_idx (date),
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
SQL
        );
        $this->addSql(
            <<<'SQL'
CREATE TABLE committee_membership_history_referent_tag (
    committee_membership_history_id INT UNSIGNED NOT NULL,
    referent_tag_id INT UNSIGNED NOT NULL,
    INDEX IDX_B6A8C718123C64CE (committee_membership_history_id), 
    INDEX IDX_B6A8C7189C262DB3 (referent_tag_id), 
    PRIMARY KEY(committee_membership_history_id, referent_tag_id)
) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
SQL
        );

        $joinAction = CommitteeMembershipAction::JOIN;

        $this->addSql(
            <<<SQL
start transaction;
 
delete from committee_membership_history_referent_tag;
delete from committees_membership_histories;

insert into committees_membership_histories(committee_id,adherent_uuid,action,privilege,date)
  select cm.committee_id, adherent.uuid, '$joinAction', cm.privilege, cm.joined_at
  from committees_memberships as cm
    join adherents as adherent on adherent.id = cm.adherent_id;

insert into committee_membership_history_referent_tag (committee_membership_history_id, referent_tag_id)
  select history.id, tag.referent_tag_id
  from committees_membership_histories as history
    join committee_referent_tag as tag on tag.committee_id = history.committee_id;

commit;
SQL
        );

        $this->addSql('ALTER TABLE committees_membership_histories ADD CONSTRAINT FK_4BBAE2C7ED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id)');
        $this->addSql('ALTER TABLE committee_membership_history_referent_tag ADD CONSTRAINT FK_B6A8C718123C64CE FOREIGN KEY (committee_membership_history_id) REFERENCES committees_membership_histories (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE committee_membership_history_referent_tag ADD CONSTRAINT FK_B6A8C7189C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE committee_membership_history_referent_tag');
        $this->addSql('DROP TABLE committees_membership_histories');
    }
}
