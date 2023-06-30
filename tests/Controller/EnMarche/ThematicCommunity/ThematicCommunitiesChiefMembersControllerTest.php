<?php

namespace Tests\App\Controller\EnMarche\ThematicCommunity;

use Tests\App\AbstractEnMarcheWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

class ThematicCommunitiesChiefMembersControllerTest extends AbstractEnMarcheWebTestCase
{
    use ControllerTestTrait;

    public function testThematicCommunityMembersFilter()
    {
        $this->authenticateAsAdherent($this->client, 'michel.vasseur@example.ch');
        $crawler = $this->client->request('GET', '/communautes-thematiques/membres');

        $crawler = $this->client->submit($crawler->selectButton('Appliquer')->form([
            'f[lastName]' => 'bert',
        ]));
        $this->assertCount(1, $crawler->filter('table tbody tr.referent__item'));
        $this->assertStringContainsString('Berthoux Gisele', $crawler->filter('table tbody tr.referent__item td')->first()->text());

        $crawler = $this->client->submit($crawler->selectButton('Appliquer')->form([
            'f[lastName]' => null,
            'f[gender]' => 'male',
        ]));

        $this->assertCount(3, $crawler->filter('table tbody tr.referent__item'));
        $this->assertStringNotContainsString('Berthoux Gisele', $crawler->filter('table tbody')->text());

        $crawler = $this->client->submit($crawler->selectButton('Appliquer')->form([
            'f[gender]' => 'male',
            'f[ageMin]' => 40,
        ]));
        $this->assertCount(1, $crawler->filter('table tbody tr.referent__item'));
        $this->assertStringNotContainsString('Peter John', $crawler->filter('table tbody')->text());

        $crawler = $this->client->submit($crawler->selectButton('Appliquer')->form([
            'f[gender]' => '',
            'f[ageMin]' => null,
            'f[emailSubscription]' => true,
        ]));
        $this->assertCount(0, $crawler->filter('table tbody tr.referent__item'));

        $crawler = $this->client->submit($crawler->selectButton('Appliquer')->form([
            'f[emailSubscription]' => '',
            'f[smsSubscription]' => true,
        ]));
        $this->assertCount(1, $crawler->filter('table tbody tr.referent__item'));
        $this->assertStringContainsString('Berthoux Gisele', $crawler->filter('table tbody tr.referent__item td')->first()->text());

        $crawler = $this->client->submit($crawler->selectButton('Appliquer')->form([
            'f[smsSubscription]' => '',
            'f[thematicCommunities]' => [1], // Santé
        ]));
        $this->assertCount(2, $crawler->filter('table tbody tr.referent__item'));
        $this->assertStringNotContainsString('Referent Referent', $crawler->filter('table tbody tr.referent__item')->text());

        $crawler = $this->client->submit($crawler->selectButton('Appliquer')->form([
            'f[thematicCommunities]' => [1], // Santé
            'f[with_job]' => true,
        ]));
        $this->assertCount(1, $crawler->filter('table tbody tr.referent__item'));
        $this->assertStringContainsString('Peter John', $crawler->filter('table tbody tr.referent__item td')->first()->text());
    }

    public function testThematicCommunityMembersOrder()
    {
        $this->authenticateAsAdherent($this->client, 'michel.vasseur@example.ch');
        $crawler = $this->client->request('GET', '/communautes-thematiques/membres');

        $crawler = $this->client->click($crawler->selectLink('Identité')->link());
        $this->assertStringContainsString('Berthoux Gisele', $crawler->filter('table tbody tr.referent__item')->eq(0)->filter('td')->first()->text());
        $this->assertStringContainsString('Peter John', $crawler->filter('table tbody tr.referent__item')->eq(1)->filter('td')->first()->text());
        $this->assertStringContainsString('Peter John', $crawler->filter('table tbody tr.referent__item')->eq(2)->filter('td')->first()->text());
        $this->assertStringContainsString('Referent Referent', $crawler->filter('table tbody tr.referent__item')->eq(3)->filter('td')->first()->text());

        $crawler = $this->client->click($crawler->selectLink('Identité')->link());
        $this->assertStringContainsString('Referent Referent', $crawler->filter('table tbody tr.referent__item')->eq(0)->filter('td')->first()->text());
        $this->assertStringContainsString('Peter John', $crawler->filter('table tbody tr.referent__item')->eq(1)->filter('td')->first()->text());
        $this->assertStringContainsString('Peter John', $crawler->filter('table tbody tr.referent__item')->eq(2)->filter('td')->first()->text());
        $this->assertStringContainsString('Berthoux Gisele', $crawler->filter('table tbody tr.referent__item')->eq(3)->filter('td')->first()->text());
    }
}
