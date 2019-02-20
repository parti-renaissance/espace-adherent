<?php

namespace Migrations;

use AppBundle\DataFixtures\ORM\LoadOrderSectionData;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20170830103508 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE order_articles (id INT AUTO_INCREMENT NOT NULL, media_id BIGINT DEFAULT NULL, position SMALLINT NOT NULL, published TINYINT(1) NOT NULL, display_media TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, title VARCHAR(100) NOT NULL, slug VARCHAR(100) NOT NULL, description VARCHAR(255) NOT NULL, twitter_description VARCHAR(255) DEFAULT NULL, keywords VARCHAR(255) DEFAULT NULL, content LONGTEXT NOT NULL, amp_content LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_5E25D3D9989D9B62 (slug), INDEX IDX_5E25D3D9EA9FDD75 (media_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE order_section_order_article (order_article_id INT NOT NULL, order_section_id INT NOT NULL, INDEX IDX_69D950ADC14E7BC9 (order_article_id), INDEX IDX_69D950AD6BF91E2F (order_section_id), PRIMARY KEY(order_article_id, order_section_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE order_sections (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, position SMALLINT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE order_articles ADD CONSTRAINT FK_5E25D3D9EA9FDD75 FOREIGN KEY (media_id) REFERENCES medias (id)');
        $this->addSql('ALTER TABLE order_section_order_article ADD CONSTRAINT FK_69D950ADC14E7BC9 FOREIGN KEY (order_article_id) REFERENCES order_articles (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE order_section_order_article ADD CONSTRAINT FK_69D950AD6BF91E2F FOREIGN KEY (order_section_id) REFERENCES order_sections (id) ON DELETE CASCADE');
    }

    public function postUp(Schema $schema): void
    {
        $position = 0;
        foreach (LoadOrderSectionData::ORDER_SECTION as $sectionName) {
            $this->connection->insert('order_sections', ['position' => ++$position, 'name' => $sectionName]);
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE order_section_order_article DROP FOREIGN KEY FK_69D950ADC14E7BC9');
        $this->addSql('ALTER TABLE order_section_order_article DROP FOREIGN KEY FK_69D950AD6BF91E2F');
        $this->addSql('DROP TABLE order_articles');
        $this->addSql('DROP TABLE order_section_order_article');
        $this->addSql('DROP TABLE order_sections');
    }
}
