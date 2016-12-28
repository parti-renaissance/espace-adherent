<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Content\ArticleRepository;
use AppBundle\Content\Model\HomeArticle;
use AppBundle\Content\Model\HomeLiveLink;
use League\CommonMark\CommonMarkConverter;
use League\Flysystem\Filesystem;
use League\Flysystem\Memory\MemoryAdapter;
use Monolog\Handler\TestHandler;
use Monolog\Logger;

class ArticleRepositoryTest extends \PHPUnit_Framework_TestCase
{
    public function testGetValidHomeItems()
    {
        $filesystem = new Filesystem(new MemoryAdapter());
        $filesystem->write('home.yml', file_get_contents(__DIR__.'/../../Fixtures/filesystem/home_valid.yml'));

        $loggerHandler = new TestHandler();

        $repository = new ArticleRepository($filesystem, new CommonMarkConverter(), new Logger('enmarche_tests', [$loggerHandler]));

        $items = $repository->getHomeArticles();

        $this->assertCount(2, $items);
        $this->assertEmpty($loggerHandler->getRecords());

        $this->assertInstanceOf(HomeArticle::class, $items['banner']);
        $this->assertEquals('article', $items['banner']->getType());
        $this->assertEquals('Nous constatons qu’au-delà des droits formels que les femmes ont obtenus, nous devons passer aux droits réels.', $items['banner']->getSubtitle());
        $this->assertEquals('Signez l’appel « Elles Marchent »', $items['banner']->getTitle());
        $this->assertEquals('https://en-marche.fr/wp-content/uploads/2016/12/Ellesmarchent_site_2.jpg', $items['banner']->getImage());
        $this->assertEquals('https://en-marche.fr/signez-lappel-marchent/', $items['banner']->getLink());

        $this->assertInstanceOf(HomeArticle::class, $items['row1_col1']);
        $this->assertEquals('video', $items['row1_col1']->getType());
        $this->assertEquals('Tribune de Richard Ferrand', $items['row1_col1']->getTitle());
        $this->assertEquals('', $items['row1_col1']->getSubtitle());
        $this->assertEquals('https://pbs.twimg.com/profile_images/664015046633238528/KlAMkY_B.jpg', $items['row1_col1']->getImage());
        $this->assertEquals('http://www.richardferrand.fr/2016/12/quand-le-ministre-eckert-fait-expres-ou-pas-de-ne-pas-comprendre/', $items['row1_col1']->getLink());
    }

    public function testGetValidHomeLiveLinks()
    {
        $filesystem = new Filesystem(new MemoryAdapter());
        $filesystem->write('home.yml', file_get_contents(__DIR__.'/../../Fixtures/filesystem/home_valid.yml'));

        $loggerHandler = new TestHandler();

        $repository = new ArticleRepository($filesystem, new CommonMarkConverter(), new Logger('enmarche_tests', [$loggerHandler]));

        $items = $repository->getHomeLiveLinks();

        $this->assertCount(2, $items);
        $this->assertEmpty($loggerHandler->getRecords());

        $this->assertInstanceOf(HomeLiveLink::class, $items[0]);
        $this->assertEquals('Guadeloupe', $items[0]->getTitle());
        $this->assertEquals('https://en-marche.fr/outre-mer-lun-piliers-de-richesse-culturelle/', $items[0]->getLink());

        $this->assertInstanceOf(HomeLiveLink::class, $items[1]);
        $this->assertEquals('Quand le ministre Eckert', $items[1]->getTitle());
        $this->assertEquals('http://www.richardferrand.fr/2016/12/quand-le-ministre-eckert-fait-expres-ou-pas-de-ne-pas-comprendre/', $items[1]->getLink());
    }

    public function testGetInvalidYamlHomeItems()
    {
        $filesystem = new Filesystem(new MemoryAdapter());
        $filesystem->write('home.yml', file_get_contents(__DIR__.'/../../Fixtures/filesystem/home_invalid_yaml.yml'));

        $loggerHandler = new TestHandler();

        $repository = new ArticleRepository($filesystem, new CommonMarkConverter(), new Logger('enmarche_tests', [$loggerHandler]));

        $this->assertEmpty($repository->getHomeArticles());
        $this->assertTrue($loggerHandler->hasCriticalThatContains('Home articles can not be read'));
    }

    public function testGetInvalidTypeHomeItems()
    {
        $filesystem = new Filesystem(new MemoryAdapter());
        $filesystem->write('home.yml', file_get_contents(__DIR__.'/../../Fixtures/filesystem/home_invalid_type.yml'));

        $loggerHandler = new TestHandler();

        $repository = new ArticleRepository($filesystem, new CommonMarkConverter(), new Logger('enmarche_tests', [$loggerHandler]));

        $this->assertEmpty($repository->getHomeArticles());
        $this->assertTrue($loggerHandler->hasCriticalThatContains('Home articles can not be read'));
    }

    public function testGetInvalidTypeHomeLiveLinks()
    {
        $filesystem = new Filesystem(new MemoryAdapter());
        $filesystem->write('home.yml', file_get_contents(__DIR__.'/../../Fixtures/filesystem/home_invalid_type.yml'));

        $loggerHandler = new TestHandler();

        $repository = new ArticleRepository($filesystem, new CommonMarkConverter(), new Logger('enmarche_tests', [$loggerHandler]));

        $this->assertEmpty($repository->getHomeLiveLinks());
        $this->assertTrue($loggerHandler->hasCriticalThatContains('Live links can not be read'));
    }

    public function testGetInexistentHomeItems()
    {
        $filesystem = new Filesystem(new MemoryAdapter());
        $loggerHandler = new TestHandler();

        $repository = new ArticleRepository($filesystem, new CommonMarkConverter(), new Logger('enmarche_tests', [$loggerHandler]));

        $this->assertEmpty($repository->getHomeArticles());
        $this->assertTrue($loggerHandler->hasCriticalThatContains('Home articles can not be read'));
    }

    public function testGetValidArticle()
    {
        $filesystem = new Filesystem(new MemoryAdapter());
        $filesystem->write('articles/slug-foo-bar/metadata.yml', file_get_contents(__DIR__.'/../../Fixtures/filesystem/article_valid_metadata.yml'));
        $filesystem->write('articles/slug-foo-bar/content.md', file_get_contents(__DIR__.'/../../Fixtures/filesystem/article_valid_content.md'));

        $loggerHandler = new TestHandler();

        $repository = new ArticleRepository($filesystem, new CommonMarkConverter(), new Logger('enmarche_tests', [$loggerHandler]));

        $article = $repository->getArticle('slug-foo-bar');

        $this->assertEmpty($loggerHandler->getRecords());
        $this->assertEquals('« Les outre-mer sont l’un des piliers de notre richesse culturelle. »', $article->getTitle());
        $this->assertEquals('Emmanuel Macron s’est rendu du 17 au 21 décembre 2016 en Guadeloupe, Martinique et Guyane.', $article->getDescription());
        $this->assertEquals(file_get_contents(__DIR__.'/../../Fixtures/filesystem/article_valid_content_expected.html'), $article->getContent());
    }

    /**
     * @expectedException \Symfony\Component\Yaml\Exception\ParseException
     */
    public function testGetInvalidMetadataArticle()
    {
        $filesystem = new Filesystem(new MemoryAdapter());
        $filesystem->write('articles/slug-foo-bar/metadata.yml', file_get_contents(__DIR__.'/../../Fixtures/filesystem/article_invalid_metadata.yml'));
        $filesystem->write('articles/slug-foo-bar/content.md', file_get_contents(__DIR__.'/../../Fixtures/filesystem/article_valid_content.md'));

        $loggerHandler = new TestHandler();

        $repository = new ArticleRepository($filesystem, new CommonMarkConverter(), new Logger('enmarche_tests', [$loggerHandler]));
        $repository->getArticle('slug-foo-bar');
    }

    public function testGetInexistentArticle()
    {
        $filesystem = new Filesystem(new MemoryAdapter());
        $loggerHandler = new TestHandler();

        $repository = new ArticleRepository($filesystem, new CommonMarkConverter(), new Logger('enmarche_tests', [$loggerHandler]));

        $article = $repository->getArticle('inexistent');

        $this->assertNull($article);
        $this->assertEmpty($loggerHandler->getRecords());
    }
}
