<?php

declare(strict_types=1);

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autoconfigure()
        ->autowire()
        ->bind('$adherentInterests', '%adherent_interests%')
        ->bind('$mailchimpSignUpHost', '%env(MAILCHIMP_SIGNUP_HOST)%');

    $services->instanceof(App\Mailchimp\Webhook\Handler\WebhookHandlerInterface::class)
        ->tag('app.mailchimp.webhook_handler');

    $services->instanceof(App\Mailchimp\Campaign\ContentSection\ContentSectionBuilderInterface::class)
        ->tag('app.mailchimp.campaign.content_builder');

    $services->instanceof(App\Mailchimp\Campaign\SegmentConditionBuilder\SegmentConditionBuilderInterface::class)
        ->tag('app.mailchimp.campaign.segment_condition_builder');

    $services->load('App\Mailchimp\\', __DIR__.'/../../src/Mailchimp/');

    $services->set(App\Mailchimp\Driver::class)
        ->arg('$listId', '%env(MAILCHIMP_MEMBER_LIST_ID)%')
        ->tag('monolog.logger', [
            'channel' => 'mailchimp_sync',
        ])
        ->call('setLogger', [
            service('logger'),
        ]);

    $services->set(App\Mailchimp\Manager::class)
        ->arg('$requestBuildersLocator', service('app.mailchimp.request_builders_locator'))
        ->tag('monolog.logger', [
            'channel' => 'mailchimp_sync',
        ])
        ->call('setLogger', [
            service('logger'),
        ]);

    $services->set(App\Mailchimp\Synchronisation\MemberRequest\NewsletterMemberRequestBuilder::class)
        ->share(false);

    $services->set(App\Mailchimp\Webhook\WebhookHandler::class)
        ->args([
            tagged_iterator('app.mailchimp.webhook_handler'),
        ]);

    $services->set(App\Mailchimp\Campaign\SegmentConditionsBuilder::class)
        ->arg('$builders', tagged_iterator('app.mailchimp.campaign.segment_condition_builder'));

    $services->set(App\Mailchimp\Synchronisation\Handler\AdherentChangeEmailCommandHandler::class)
        ->tag('monolog.logger', [
            'channel' => 'mailchimp_sync',
        ])
        ->call('setLogger', [
            service('logger'),
        ]);

    $services->set(App\Mailchimp\Synchronisation\Handler\ElectedRepresentativeChangeCommandHandler::class)
        ->tag('monolog.logger', [
            'channel' => 'mailchimp_sync',
        ])
        ->call('setLogger', [
            service('logger'),
        ]);

    $services->set(App\Mailchimp\Synchronisation\Handler\JemarcheDataSurveyCreateCommandHandler::class)
        ->tag('monolog.logger', [
            'channel' => 'mailchimp_sync',
        ])
        ->call('setLogger', [
            service('logger'),
        ]);

    $services->set(App\Mailchimp\Synchronisation\RequestBuilder::class)
        ->share(false)
        ->tag('monolog.logger', [
            'channel' => 'mailchimp_sync',
        ])
        ->call('setLogger', [
            service('logger'),
        ]);

    $services->load('App\Newsletter\Handler\\', __DIR__.'/../../src/Newsletter/Handler/');

    $services->set(App\Mailchimp\SignUp\SignUpHandler::class)
        ->arg('$subscriptionGroupId', '%env(MAILCHIMP_SIGNUP_SUBSCRIPTION_GROUP_ID)%')
        ->arg('$subscriptionIds', '%env(json:MAILCHIMP_SIGNUP_SUBSCRIPTION_IDS)%')
        ->arg('$mailchimpOrgId', '%env(MAILCHIMP_ORG_ID)%')
        ->arg('$listId', '%env(MAILCHIMP_MEMBER_LIST_ID)%')
        ->tag('monolog.logger', [
            'channel' => 'mailchimp_sync',
        ])
        ->call('setLogger', [
            service('logger'),
        ]);

    $services->set(App\Mailchimp\Campaign\MailchimpObjectIdMapping::class)
        ->arg('$mainListId', '%env(MAILCHIMP_MEMBER_LIST_ID)%')
        ->arg('$newsletterListId', '%env(MAILCHIMP_NEWSLETTER_LIST_ID)%')
        ->arg('$electedRepresentativeListId', '%env(MAILCHIMP_ELECTED_REPRESENTATIVE_LIST_ID)%')
        ->arg('$nationalEventInscriptionListId', '%env(MAILCHIMP_NATIONAL_EVENT_INSCRIPTION_LIST_ID)%')
        ->arg('$jecouteListId', '%env(MAILCHIMP_JECOUTE_LIST_ID)%')
        ->arg('$jeMengageListId', '%env(MAILCHIMP_JEMENGAGE_LIST_ID)%')
        ->arg('$newsletterLegislativeCandidateListId', '%env(MAILCHIMP_NEWSLETTER_LEGISLATIVE_CANDIDATE_LIST_ID)%')
        ->arg('$newsletterRenaissanceListId', '%env(MAILCHIMP_NEWSLETTER_RENAISSANCE_LIST_ID)%')
        ->arg('$folderIds', '%env(json:MAILCHIMP_CAMPAIGN_FOLDER_IDS)%')
        ->arg('$templateIds', '%env(json:MAILCHIMP_TEMPLATE_IDS)%')
        ->arg('$interestIds', '%env(json:MAILCHIMP_INTEREST_IDS)%')
        ->arg('$memberGroupInterestGroupId', '%env(MAILCHIMP_MEMBER_GROUP_INTEREST_GROUP_ID)%')
        ->arg('$memberInterestInterestGroupId', '%env(MAILCHIMP_MEMBER_INTEREST_INTEREST_GROUP_ID)%')
        ->arg('$subscriptionTypeInterestGroupId', '%env(MAILCHIMP_SUBSCRIPTION_TYPE_INTEREST_GROUP_ID)%')
        ->arg('$mailchimpCampaignUrl', '%env(MAILCHIMP_CAMPAIGN_URL)%')
        ->arg('$mailchimpOrg', '%env(MAILCHIMP_ORG_ID)%');

    $services->set('app.mailchimp.request_builders_locator', Symfony\Component\DependencyInjection\ServiceLocator::class)
        ->args([
            [
                App\Mailchimp\Synchronisation\MemberRequest\NewsletterMemberRequestBuilder::class => service(App\Mailchimp\Synchronisation\MemberRequest\NewsletterMemberRequestBuilder::class),
                App\Mailchimp\Synchronisation\RequestBuilder::class => service(App\Mailchimp\Synchronisation\RequestBuilder::class),
                App\Mailchimp\Campaign\CampaignRequestBuilder::class => service(App\Mailchimp\Campaign\CampaignRequestBuilder::class),
                App\Mailchimp\Campaign\CampaignContentRequestBuilder::class => service(App\Mailchimp\Campaign\CampaignContentRequestBuilder::class),
                App\Mailchimp\MailchimpSegment\SegmentRequestBuilder::class => service(App\Mailchimp\MailchimpSegment\SegmentRequestBuilder::class),
            ],
        ])
        ->tag('container.service_locator');
};
