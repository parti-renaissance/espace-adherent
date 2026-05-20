<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\Email\TransactionalEmailTemplate;
use App\Entity\Renaissance\NewsletterSource;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadRenaissanceNewsletterSourceData extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $manager->persist($this->createSource(
            code: 'site_renaissance',
            label: 'Site Renaissance',
            mailchimpTag: null,
            redirectUrl: null,
            enabled: true,
        ));

        $manager->persist($this->createSource(
            code: 'site_eu',
            label: 'Europe',
            mailchimpTag: 'Europe 2026',
            redirectUrl: 'https://legislatives.parti-renaissance.dev/confirmation-newsletter',
            enabled: true,
        ));

        $manager->persist($this->createSource(
            code: 'site_ensemble',
            label: 'Ensemble',
            mailchimpTag: 'Ensemble',
            redirectUrl: 'https://legislatives.parti-renaissance.dev/confirmation-newsletter',
            enabled: true,
            confirmationEmailTemplate: $this->getReference(
                LoadTransactionalEmailTemplateData::REFERENCE_NEWSLETTER_CONFIRMATION,
                TransactionalEmailTemplate::class,
            ),
        ));

        $manager->persist($this->createSource(
            code: 'campagne_test_desactivee',
            label: 'Campagne de test (désactivée)',
            mailchimpTag: null,
            redirectUrl: null,
            enabled: false,
        ));

        $manager->flush();
    }

    private function createSource(
        string $code,
        string $label,
        ?string $mailchimpTag,
        ?string $redirectUrl,
        bool $enabled,
        ?TransactionalEmailTemplate $confirmationEmailTemplate = null,
    ): NewsletterSource {
        $source = new NewsletterSource();
        $source->code = $code;
        $source->label = $label;
        $source->mailchimpTag = $mailchimpTag;
        $source->confirmationRedirectUrl = $redirectUrl;
        $source->enabled = $enabled;
        $source->confirmationEmailTemplate = $confirmationEmailTemplate;

        return $source;
    }

    public function getDependencies(): array
    {
        return [LoadTransactionalEmailTemplateData::class];
    }
}
