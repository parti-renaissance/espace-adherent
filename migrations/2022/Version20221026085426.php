<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221026085426 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_message_filters ADD renaissance_membership VARCHAR(255) DEFAULT NULL');
        $this->addSql('UPDATE adherent_message_filters SET renaissance_membership = :adherent_re WHERE is_renaissance_membership IS TRUE', [
            'adherent_re' => 'adherent_re',
        ]);
        $this->addSql('ALTER TABLE adherent_message_filters DROP is_renaissance_membership');

        $this->addSql('ALTER TABLE audience ADD renaissance_membership VARCHAR(255) DEFAULT NULL');
        $this->addSql('UPDATE audience SET renaissance_membership = :adherent_re WHERE is_renaissance_membership IS TRUE', [
            'adherent_re' => 'adherent_re',
        ]);
        $this->addSql('ALTER TABLE audience DROP is_renaissance_membership');

        $this->addSql('ALTER TABLE audience_snapshot ADD renaissance_membership VARCHAR(255) DEFAULT NULL');
        $this->addSql('UPDATE audience_snapshot SET renaissance_membership = :adherent_re WHERE is_renaissance_membership IS TRUE', [
            'adherent_re' => 'adherent_re',
        ]);
        $this->addSql('ALTER TABLE audience_snapshot DROP is_renaissance_membership');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_message_filters ADD is_renaissance_membership TINYINT(1) DEFAULT NULL');
        $this->addSql('UPDATE adherent_message_filters SET is_renaissance_membership = TRUE WHERE renaissance_membership = adherent_re', [
            'adherent_re' => 'adherent_re',
        ]);
        $this->addSql('ALTER TABLE adherent_message_filters DROP renaissance_membership');

        $this->addSql('ALTER TABLE audience ADD is_renaissance_membership TINYINT(1) DEFAULT NULL');
        $this->addSql('UPDATE audience SET is_renaissance_membership = TRUE WHERE renaissance_membership = adherent_re', [
            'adherent_re' => 'adherent_re',
        ]);
        $this->addSql('ALTER TABLE audience DROP renaissance_membership');
    }
}
