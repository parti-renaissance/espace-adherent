<?php

declare(strict_types=1);

namespace Tests\App\Controller\Renaissance\NationalEvent;

use App\Entity\NationalEvent\NationalEvent;
use App\Repository\NationalEvent\NationalEventRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
class EventInscriptionContentRenderingTest extends AbstractWebTestCase
{
    use ControllerTestTrait;

    private ?EntityManagerInterface $em = null;

    public function testBackOfficeIntroKeepsItsBlankLinesAndListsOnTheInscriptionPage(): void
    {
        // Shaped exactly as the editor serialises it: a blank line is an empty
        // paragraph, and every list item nests a paragraph of its own.
        $this->setTextIntro(
            '<p><strong>Le 10 octobre, rendez-vous à Lyon.</strong></p>'
            .'<p></p>'
            .'<p>Face à un pays tenté par les extrêmes.</p>'
            .'<ul><li><p>Premier point</p></li><li><p>Second point</p></li></ul>'
        );

        $crawler = $this->client->request(Request::METHOD_GET, '/grand-rassemblement/event-national-1');
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $scope = $crawler->filter('.formatted-text');
        self::assertGreaterThan(0, $scope->count(), 'The editor content must be rendered inside its styling scope.');

        $intro = $scope->reduce(function ($node): bool {
            return str_contains($node->text(), 'rendez-vous à Lyon');
        })->first();

        self::assertSame(1, $intro->count(), 'The intro must be wrapped by the styling scope.');

        $paragraphs = $intro->children('p');
        self::assertSame(3, $paragraphs->count(), 'The blank line must survive as an empty paragraph.');
        self::assertSame('', trim($paragraphs->eq(1)->html()), 'The blank line must stay empty.');

        self::assertSame(2, $intro->filter('ul > li > p')->count(), 'The list must survive as a real list.');
    }

    private function setTextIntro(string $html): void
    {
        /** @var NationalEventRepository $repository */
        $repository = $this->getRepository(NationalEvent::class);

        $event = $repository->findOneBy(['slug' => 'event-national-1']);
        self::assertInstanceOf(NationalEvent::class, $event);

        $event->textIntro = $html;
        $this->em->flush();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->em = $this->getEntityManager(NationalEvent::class);

        $this->client->setServerParameter('HTTP_HOST', static::getContainer()->getParameter('user_vox_host'));
    }

    protected function tearDown(): void
    {
        $this->em = null;

        parent::tearDown();
    }
}
