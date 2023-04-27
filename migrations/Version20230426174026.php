<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230426174026 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_message_filters DROP FOREIGN KEY FK_28CA9F94ED1A100B');
        $this->addSql('ALTER TABLE
          adherent_message_filters
        ADD
          CONSTRAINT FK_28CA9F94ED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE committee_feed_item DROP FOREIGN KEY FK_4F1CDC80ED1A100B');
        $this->addSql('ALTER TABLE
          committee_feed_item
        ADD
          CONSTRAINT FK_4F1CDC80ED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE committees_membership_histories DROP FOREIGN KEY FK_4BBAE2C7ED1A100B');
        $this->addSql('ALTER TABLE
          committees_membership_histories
        ADD
          CONSTRAINT FK_4BBAE2C7ED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE committees_memberships DROP FOREIGN KEY FK_E7A6490EED1A100B');
        $this->addSql('ALTER TABLE
          committees_memberships
        ADD
          CONSTRAINT FK_E7A6490EED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE events DROP FOREIGN KEY FK_5387574AED1A100B');
        $this->addSql('ALTER TABLE
          events
        ADD
          CONSTRAINT FK_5387574AED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_message_filters DROP FOREIGN KEY FK_28CA9F94ED1A100B');
        $this->addSql('ALTER TABLE
          adherent_message_filters
        ADD
          CONSTRAINT FK_28CA9F94ED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE committee_feed_item DROP FOREIGN KEY FK_4F1CDC80ED1A100B');
        $this->addSql('ALTER TABLE
          committee_feed_item
        ADD
          CONSTRAINT FK_4F1CDC80ED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE committees_membership_histories DROP FOREIGN KEY FK_4BBAE2C7ED1A100B');
        $this->addSql('ALTER TABLE
          committees_membership_histories
        ADD
          CONSTRAINT FK_4BBAE2C7ED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE committees_memberships DROP FOREIGN KEY FK_E7A6490EED1A100B');
        $this->addSql('ALTER TABLE
          committees_memberships
        ADD
          CONSTRAINT FK_E7A6490EED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE events DROP FOREIGN KEY FK_5387574AED1A100B');
        $this->addSql('ALTER TABLE
          events
        ADD
          CONSTRAINT FK_5387574AED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
