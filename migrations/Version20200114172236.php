<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200114172236 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE donator_kinship (id INT UNSIGNED AUTO_INCREMENT NOT NULL, donator_id INT UNSIGNED NOT NULL, related_id INT UNSIGNED NOT NULL, kinship VARCHAR(100) NOT NULL, INDEX IDX_E542211D831BACAF (donator_id), INDEX IDX_E542211D4162C001 (related_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE donator_kinship ADD CONSTRAINT FK_E542211D831BACAF FOREIGN KEY (donator_id) REFERENCES donators (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE donator_kinship ADD CONSTRAINT FK_E542211D4162C001 FOREIGN KEY (related_id) REFERENCES donators (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE donator_kinship');
    }
}
