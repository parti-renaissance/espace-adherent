<?php

namespace Tests\App\Controller\Admin\ElectedRepresentative;

use App\DataFixtures\ORM\LoadElectedRepresentativeData;
use App\Entity\ElectedRepresentative\ElectedRepresentative;
use App\Mailchimp\Synchronisation\Command\ElectedRepresentativeChangeCommand;
use App\Mailchimp\Synchronisation\Command\ElectedRepresentativeArchiveCommand;
use App\Repository\ElectedRepresentative\ElectedRepresentativeRepository;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\App\Controller\ControllerTestTrait;
use Tests\App\MessengerTestTrait;

/**
 * @group functional
 * @group admin
 */
class ElectedRepresentativeAdminTest extends WebTestCase
{
    private const EDIT_URI_PATTERN = '/admin/app/electedrepresentative-electedrepresentative/%s/edit';

    use ControllerTestTrait;
    use MessengerTestTrait;

    /**
     * @var ElectedRepresentativeRepository
     */
    private $electedRepresentativeRepository;

    /**
     * @dataProvider provideUpdateTriggerMessage
     */
    public function testUpdateTriggerMessage(
        array $values,
        bool $isChangeMessageExpected,
        bool $isDeleteMessageExpected
    ): void {
        $this->authenticateAsAdmin($this->client);

        /** @var ElectedRepresentative $electedRepresentative */
        $electedRepresentative = $this->electedRepresentativeRepository->findOneByUuid(LoadElectedRepresentativeData::ELECTED_REPRESENTATIVE_1_UUID);

        $editUrl = sprintf(self::EDIT_URI_PATTERN, $electedRepresentative->getId());
        $crawler = $this->client->request('GET', $editUrl);
        $this->assertStatusCode(200, $this->client);

        $form = $crawler->selectButton('Mettre à jour')->form();
        $formName = str_replace(
            sprintf('%s?uniqid=', $editUrl),
            '',
            $form->getFormNode()->getAttribute('action')
        );

        $formValues = [];
        foreach ($values as $key => $value) {
            $formValues[$formName."[$key]"] = $value;
        }

        $this->client->submit($crawler->selectButton('Mettre à jour')->form($formValues));
        $this->assertResponseStatusCode('302', $this->client->getResponse());

        if ($isChangeMessageExpected) {
            $this->assertMessageIsDispatched(ElectedRepresentativeChangeCommand::class);
        } else {
            $this->assertMessageIsNotDispatched(ElectedRepresentativeChangeCommand::class);
        }

        if ($isDeleteMessageExpected) {
            $this->assertMessageIsDispatched(ElectedRepresentativeArchiveCommand::class);
        } else {
            $this->assertMessageIsNotDispatched(ElectedRepresentativeArchiveCommand::class);
        }

        $this->client->followRedirect();
        $this->assertStatusCode(200, $this->client);
    }

    public function provideUpdateTriggerMessage(): iterable
    {
        yield [['lastName' => 'DUFOUR2'], true, false];
        yield [['lastName' => 'DUFOUR'], false, false];
        yield [['firstName' => 'Michel'], true, false];
        yield [['gender' => 'male'], true, false];
        yield [['gender' => 'female'], false, false];
        yield [['birthDate' => '27 nov. 1960'], true, false];
        yield [['birthDate' => '23 nov. 1972'], false, false];
        yield [['adherent' => 'jacques.picard@en-marche.fr'], true, false];
        yield [['adherent' => 'gisele-berthoux@caramail.com'], false, false];
        yield [['adherent' => null, 'isAdherent' => '0'], false, true];
        yield [['adherent' => null, 'isAdherent' => '0', 'contactEmail' => 'gisele-berthoux@caramail.com'], true, false];
        yield [[], false, false];
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init();

        $this->electedRepresentativeRepository = $this->getElectedRepresentativeRepository();
    }

    protected function tearDown()
    {
        $this->kill();

        $this->electedRepresentativeRepository = null;

        parent::tearDown();
    }
}
