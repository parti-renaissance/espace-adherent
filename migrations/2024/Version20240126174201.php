<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240126174201 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_messages DROP FOREIGN KEY FK_D187C183F675F31B');
        $this->addSql('ALTER TABLE adherent_messages CHANGE author_id author_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE
          adherent_messages
        ADD
          CONSTRAINT FK_D187C183F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE committees_memberships DROP FOREIGN KEY FK_E7A6490E25F06C53');
        $this->addSql('ALTER TABLE
          committees_memberships
        ADD
          CONSTRAINT FK_E7A6490E25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE my_team_delegated_access DROP FOREIGN KEY FK_421C13B9B7E7AE18');
        $this->addSql('ALTER TABLE my_team_delegated_access CHANGE delegated_id delegated_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE
          my_team_delegated_access
        ADD
          CONSTRAINT FK_421C13B9B7E7AE18 FOREIGN KEY (delegated_id) REFERENCES adherents (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_messages DROP FOREIGN KEY FK_D187C183F675F31B');
        $this->addSql('ALTER TABLE adherent_messages CHANGE author_id author_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          adherent_messages
        ADD
          CONSTRAINT FK_D187C183F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE committees_memberships DROP FOREIGN KEY FK_E7A6490E25F06C53');
        $this->addSql('ALTER TABLE
          committees_memberships
        ADD
          CONSTRAINT FK_E7A6490E25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE my_team_delegated_access DROP FOREIGN KEY FK_421C13B9B7E7AE18');
        $this->addSql('ALTER TABLE
          my_team_delegated_access
        CHANGE
          delegated_id delegated_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          my_team_delegated_access
        ADD
          CONSTRAINT FK_421C13B9B7E7AE18 FOREIGN KEY (delegated_id) REFERENCES adherents (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
