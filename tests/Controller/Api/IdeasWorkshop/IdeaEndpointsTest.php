<?php

namespace Tests\AppBundle\Controller\Api\IdeasWorkshop;

use AppBundle\DataFixtures\ORM\LoadIdeaData;
use AppBundle\DataFixtures\ORM\LoadUserDocumentData;
use AppBundle\Entity\IdeasWorkshop\Answer;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Ramsey\Uuid\Uuid;
use Tests\AppBundle\Controller\ControllerTestTrait;

class IdeaEndpointsTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testAddAndRemoveDocumentInIdeaAnswerContent(): void
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');
        $answer = $this->getAnswer(LoadIdeaData::IDEA_01_UUID);

        $this->assertNotNull($answer);
        $this->assertSame(0, $answer->getDocuments()->count());

        $ideaData = [
            'name' => 'New name',
            'answers' => [
                0 => [
                    'id' => 1,
                    'question' => 1,
                    'content' => 'Le contenu contient UUID d\'un document '.LoadUserDocumentData::USER_DOCUMENT_1_UUID,
                ],
            ],
        ];

        $this->client->request(
            'PUT',
            '/api/ideas-workshop/ideas/'.LoadIdeaData::IDEA_01_UUID,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($ideaData)
        );

        self::assertSame(200, $this->client->getResponse()->getStatusCode());

        // We need to reload the manager reference to get the updated data
        $this->manager->clear();
        $answer = $this->getAnswer(LoadIdeaData::IDEA_01_UUID);

        $this->assertNotNull($answer);
        $this->assertSame(1, $answer->getDocuments()->count());
        $this->assertSame('idea_document.png', $answer->getDocuments()->first()->getOriginalName());

        $ideaData = [
            'name' => 'New name',
            'answers' => [
                0 => [
                    'id' => 1,
                    'question' => 1,
                    'content' => 'Le contenu sans fichier.',
                ],
            ],
        ];

        $this->client->request(
            'PUT',
            '/api/ideas-workshop/ideas/'.LoadIdeaData::IDEA_01_UUID,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($ideaData)
        );

        self::assertSame(200, $this->client->getResponse()->getStatusCode());

        // We need to reload the manager reference to get the updated data
        $this->manager->clear();
        $answer = $this->getAnswer(LoadIdeaData::IDEA_01_UUID);

        $this->assertNotNull($answer);
        $this->assertSame(0, $answer->getDocuments()->count());
    }

    private function getAnswer(string $ideaUuid): ?Answer
    {
        return $this
            ->getRepository(Answer::class)
            ->createQueryBuilder('answer')
            ->join('answer.idea', 'idea')
            ->where('idea.uuid = :uuid')
            ->setParameter('uuid', Uuid::fromString($ideaUuid))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    protected function setUp()
    {
        $this->init();
        parent::setUp();
    }
}
