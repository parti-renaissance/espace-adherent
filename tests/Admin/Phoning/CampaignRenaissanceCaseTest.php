<?php

declare(strict_types=1);

namespace Tests\App\Admin\Phoning;

use App\Entity\Phoning\Campaign;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Tests\App\AbstractAdminWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
#[Group('admin')]
class CampaignRenaissanceCaseTest extends AbstractAdminWebTestCase
{
    use ControllerTestTrait;

    public function testAdminCanCreateCampaign(): void
    {
        $this->authenticateAsAdmin($this->client);

        $crawler = $this->client->request(Request::METHOD_GET, $url = '/app/phoning-campaign/create');

        $form = $crawler->selectButton('Créer')->form();
        $formName = str_replace(\sprintf('%s?uniqid=', $url), '', $form->getFormNode()->getAttribute('action'));

        $form[\sprintf('%s[title]', $formName)] = $title = 'Test Campaign creation';
        $form[\sprintf('%s[brief]', $formName)] = '# Description';
        $form[\sprintf('%s[goal]', $formName)] = 42;
        $form[\sprintf('%s[survey]', $formName)] = 1; // ID Survey
        $form[\sprintf('%s[finishAt]', $formName)] = '31 déc. 2042';
        $form[\sprintf('%s[team]', $formName)] = 1; // ID Team

        $this->client->submit($form);

        $campaign = $this->getRepository(Campaign::class)->findOneBy(['title' => $title]);
        $this->assertClientIsRedirectedTo(\sprintf('/app/phoning-campaign/%d/edit', $campaign->getId()), $this->client);
    }
}
