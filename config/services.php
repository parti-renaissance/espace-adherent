<?php

declare(strict_types=1);

use function Symfony\Component\DependencyInjection\Loader\Configurator\inline_service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set('locale', 'fr');

    $parameters->set('locales', [
        'fr',
        'en',
    ]);

    $parameters->set('pattern_uuid', '[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}');

    $parameters->set('pattern_sha1', '[0-9A-Fa-f]{40}');

    $parameters->set('pattern_coordinate', '-?\d+(\.\d{1,7})?');

    $parameters->set('search_max_results', 30);

    $parameters->set('enable_canary', '%env(ENABLE_CANARY)%');

    $parameters->set('transactional_sender_email', 'contact@parti-renaissance.fr');

    $parameters->set('transactional_sender_name', 'Renaissance');

    $parameters->set('app_host', '%env(APP_HOST)%');

    $parameters->set('national_event_host', '%env(NATIONAL_EVENT_HOST)%');

    $parameters->set('user_vox_host', '%env(USER_VOX_HOST)%');

    $parameters->set('formation_auth_start_url', '%env(FORMATION_URL)%/auth/oauth2/login.php?id=1&wantsurl=%2F');

    $parameters->set('legislative_host', '%env(LEGISLATIVE_HOST)%');

    $parameters->set('vox_host', '%env(VOX_HOST)%');

    $parameters->set('besoindeurope_host', '%env(BESOINDEUROPE_HOST)%');

    $parameters->set('procuration_host', '%env(PROCURATION_HOST)%');

    $parameters->set('renaissance_host', '%env(RENAISSANCE_HOST)%');

    $parameters->set('app_renaissance_host', '%env(APP_RENAISSANCE_HOST)%');

    $parameters->set('admin_renaissance_host', '%env(ADMIN_RENAISSANCE_HOST)%');

    $parameters->set('webhook_renaissance_host', '%env(WEBHOOK_RENAISSANCE_HOST)%');

    $parameters->set('production_webhook_host', '%env(PRODUCTION_WEBHOOK_HOST)%');

    $parameters->set('api_renaissance_host', '%env(API_RENAISSANCE_HOST)%');

    $parameters->set('renaissance_qrcode_host', '%env(RENAISSANCE_QRCODE_HOST)%');

    $parameters->set('mooc_base_url', '%env(MOOC_BASE_URL)%');

    $parameters->set('api_path_prefix', '%env(API_PATH_PREFIX)%');

    $parameters->set('router.request_context.scheme', '%env(APP_SCHEME)%');

    $parameters->set('router.request_context.host', '%app_host%');

    $parameters->set('image_max_length', '450px');

    $parameters->set('national_event_ticket_host', '%env(GCLOUD_NATIONAL_EVENT_BUCKET)%');

    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure()
        ->bind('$environment', '%kernel.environment%')
        ->bind('$secret', '%kernel.secret%')
        ->bind('$projectDir', '%kernel.project_dir%')
        ->bind('$canaryMode', '%env(ENABLE_CANARY)%')
        ->bind('$mailchimpListId', '%env(MAILCHIMP_MEMBER_LIST_ID)%')
        ->bind('$mailchimpElectedRepresentativeListId', '%env(MAILCHIMP_ELECTED_REPRESENTATIVE_LIST_ID)%')
        ->bind('$patternUuid', '%pattern_uuid%')
        ->bind('$adherentInterests', '%adherent_interests%')
        ->bind('$mailchimpWebhookKey', '%env(MAILCHIMP_WEBHOOK_KEY)%')
        ->bind('$renaissanceHost', '%app_renaissance_host%')
        ->bind('$adminRenaissanceHost', '%admin_renaissance_host%')
        ->bind('$jemengageHost', '%env(JEMENGAGE_HOST)%')
        ->bind('$userVoxHost', '%user_vox_host%')
        ->bind('$invalidEmailHashKey', '%env(INVALID_EMAIL_HASH_KEY)%')
        ->bind('$unlayerDefaultTemplateId', '%env(int:UNLAYER_DEFAULT_TEMPLATE_ID)%')
        ->bind('$goCardlessApiKey', '%env(GOCARDLESS_API_KEY)%')
        ->bind('$goCardlessEnvironment', '%env(GOCARDLESS_ENV)%')
        ->bind('$openAIApiKey', '%env(OPENAI_API_KEY)%')
        ->bind('$friendlyCaptchaEuropeSiteKey', '%env(FRIENDLY_CAPTCHA_EUROPE_SITE_KEY)%')
        ->bind('$friendlyCaptchaNRPSiteKey', '%env(FRIENDLY_CAPTCHA_NRP_SITE_KEY)%')
        ->bind('$systemPayMode', '%env(SYSTEMPAY_MODE)%')
        ->bind('$systemPaySiteId', '%env(SYSTEMPAY_SITE_ID)%')
        ->bind('$systemPayKey', '%env(SYSTEMPAY_KEY)%')
        ->bind('$ogonePspId', '%env(OGONE_PSPID)%')
        ->bind('$ogoneUserId', '%env(OGONE_USER_ID)%')
        ->bind('$ogoneUserPwd', '%env(OGONE_USER_PWD)%')
        ->bind('$ogoneShaInKey', '%env(OGONE_SHAINKEY)%')
        ->bind('$ogoneWebhookKey', '%env(OGONE_WEBHOOK_KEY)%')
        ->bind('$appEnvironment', '%env(APP_ENVIRONMENT)%')
        ->bind('$googlePlaceApiKey', '%env(GMAPS_PRIVATE_API_KEY)%')
        ->bind('$maxIdleTime', '%env(SESSION_MAX_IDLE_TIME)%')
        ->bind('$templateWebhookKey', '%env(TEMPLATE_WEBHOOK_KEY)%')
        ->bind('$telegramChatIdPrimoAdhesion', '%env(TELEGRAM_CHAT_ID_PRIMO_ADHESION)%')
        ->bind('$telegramChatIdDeclaredMandates', '%env(TELEGRAM_CHAT_ID_DECLARED_MANDATES)%')
        ->bind('$telegramChatIdNominations', '%env(TELEGRAM_CHAT_ID_NOMINATIONS)%')
        ->bind('$referralHost', '%env(REFERRAL_HOST)%')
        ->bind('$updateCleanedContactToken', '%env(UPDATE_CLEANED_CONTACT_TOKEN)%')
        ->bind('$updateCleanedContactApiToken', '%env(UPDATE_CLEANED_CONTACT_API_TOKEN)%');

    $services->instanceof(App\Adherent\Unregistration\Handlers\UnregistrationAdherentHandlerInterface::class)
        ->tag('app.adherent.unregistration.handler');

    $services->instanceof(App\Adherent\Certification\Handlers\CertificationRequestHandlerInterface::class)
        ->tag('app.adherent.certification_request.handler');

    $services->instanceof(App\AdherentMessage\MailchimpCampaign\Handler\MailchimpCampaignHandlerInterface::class)
        ->tag('app.adherent_message.mailchimp.campaign.handler');

    $services->instanceof(App\AdherentMessage\TransactionalMessage\MessageModifier\MessageModifierInterface::class)
        ->tag('app.adherent_message.transaction.message_modifier');

    $services->instanceof(App\AdherentMessage\Sender\SenderInterface::class)
        ->tag('app.adherent_message.sender');

    $services->instanceof(App\Redirection\Dynamic\RedirectToInterface::class)
        ->tag('app.redirection.handler');

    $services->instanceof(App\VotingPlatform\AdherentMandate\Factory\AdherentMandateFactoryInterface::class)
        ->tag('app.voting_platform.mandate_factory');

    $services->instanceof(App\VotingPlatform\Election\ResultCalculator\ResultCalculatorInterface::class)
        ->tag('app.voting_platform.result_calculator');

    $services->instanceof(App\Scope\Generator\ScopeGeneratorInterface::class)
        ->tag('app.adherent.scope_generator');

    $services->instanceof(App\JMEFilter\FilterBuilder\FilterBuilderInterface::class)
        ->tag('app.filter.builder');

    $services->instanceof(App\OAuth\App\AuthAppUrlGeneratorInterface::class)
        ->tag('app.auth_app.url_generator');

    $services->instanceof(App\Recaptcha\RecaptchaApiClientInterface::class)
        ->tag('app.recaptcha_api_client');

    $services->instanceof(App\Adherent\Tag\TagGenerator\TagGeneratorInterface::class)
        ->tag('app.adherent.tag.generator');

    $services->instanceof(App\JeMengage\Timeline\FeedProcessor\FeedProcessorInterface::class)
        ->tag('app.timeline.feed_processor');

    $services->instanceof(App\JeMengage\Alert\Provider\AlertProviderInterface::class)
        ->tag('app.alert_provider');

    $services->instanceof(App\JeMengage\Push\TokenProvider\TokenProviderInterface::class)
        ->tag('app.token_provider');

    $services->instanceof(App\JeMengage\Hit\Stats\Provider\ProviderInterface::class)
        ->tag('app.hit.stats_provider');

    $services->instanceof(App\AdherentMessage\Variable\Renderer\PublicationVariableRendererInterface::class)
        ->tag('app.publication.variable.renderer');

    $services->load('App\\', __DIR__.'/../src/*')
        ->exclude([
            __DIR__.'/../src/Entity',
            __DIR__.'/../src/Exception',
            __DIR__.'/../src/Kernel.php',
        ]);

    $services->set(App\Twig\AssetRuntime::class)
        ->arg('$appVersion', '%env(APP_VERSION)%')
        ->arg('$symfonyAssetExtension', service('twig.extension.assets'))
        ->arg('$mimeTypes', service('mime_types'));

    $services->load('App\Controller\\', __DIR__.'/../src/Controller/')
        ->tag('controller.service_arguments');

    $services->load('App\Controller\Admin\\', __DIR__.'/../src/Controller/Admin')
        ->public()
        ->tag('controller.service_arguments');

    $services->load('App\EntityListener\\', __DIR__.'/../src/EntityListener')
        ->tag('doctrine.orm.entity_listener');

    $services->set(App\Adherent\Handler\RemoveAdherentAndRelatedDataCommandHandler::class)
        ->arg('$handlers', tagged_iterator('app.adherent.unregistration.handler'));

    $services->set(App\Adherent\Certification\CertificationRequestProcessCommandHandler::class)
        ->arg('$handlers', tagged_iterator('app.adherent.certification_request.handler'));

    $services->set(App\Redirection\Dynamic\RedirectionsSubscriber::class)
        ->args([
            tagged_iterator('app.redirection.handler'),
        ]);

    $services->set(App\AdherentMessage\Listener\InitialiseMailchimpCampaignEntitySubscriber::class)
        ->args([
            tagged_iterator(tag: 'app.adherent_message.mailchimp.campaign.handler', defaultPriorityMethod: 'getPriority'),
        ]);

    $services->set(App\AdherentMessage\Listener\UpdateTransactionalAdherentMessageDataListener::class)
        ->args([
            tagged_iterator('app.adherent_message.transaction.message_modifier'),
        ]);

    $services->set(App\AdherentMessage\AdherentMessageManager::class)
        ->arg('$senders', tagged_iterator('app.adherent_message.sender'));

    $services->set(App\JeMengage\Timeline\DataProvider::class)
        ->arg('$processors', tagged_iterator('app.timeline.feed_processor'));

    $services->set(App\JeMengage\Alert\AlertProvider::class)
        ->arg('$providers', tagged_iterator('app.alert_provider'));

    $services->set(App\JeMengage\Push\SendNotificationHandler::class)
        ->arg('$tokenProviders', tagged_iterator('app.token_provider'));

    $services->set(App\JeMengage\Hit\Stats\Aggregator::class)
        ->arg('$providers', tagged_iterator('app.hit.stats_provider'));

    $services->alias(App\JeMengage\Hit\Stats\AggregatorInterface::class, App\JeMengage\Hit\Stats\Aggregator::class);

    $services->set(App\Adherent\Tag\TagAggregator::class)
        ->arg('$generators', tagged_iterator(tag: 'app.adherent.tag.generator'));

    $services->set(App\OAuth\App\AuthAppUrlManager::class)
        ->args([
            tagged_iterator(tag: 'app.auth_app.url_generator', defaultIndexMethod: 'getAppCode'),
        ]);

    $services->set('app.ssl_private_key', League\OAuth2\Server\CryptKey::class)
        ->arg('$keyPath', '%env(SSL_PRIVATE_KEY)%')
        ->arg('$keyPermissionsCheck', '%env(bool:SSL_KEY_PERMISSIONS_CHECK)%');

    $services->set('app.ssl_public_key', League\OAuth2\Server\CryptKey::class)
        ->arg('$keyPath', '%env(SSL_PUBLIC_KEY)%')
        ->arg('$keyPermissionsCheck', '%env(bool:SSL_KEY_PERMISSIONS_CHECK)%');

    $services->set(App\OAuth\Repository\OAuthUserRepository::class)
        ->arg('$userProvider', service('security.user.provider.concrete.users_db'));

    $services->set(App\OAuth\AuthorizationServerFactory::class)
        ->arg('$privateKey', service('app.ssl_private_key'))
        ->arg('$encryptionKey', '%env(SSL_ENCRYPTION_KEY)%')
        ->arg('$accessTokenTtlInterval', '%env(ACCESS_TOKEN_TTL_INTERVAL)%')
        ->arg('$refreshTokenTtlInterval', '%env(REFRESH_TOKEN_TTL_INTERVAL)%');

    $services->set(App\Normalizer\JecouteDeviceNormalizer::class)
        ->tag('serializer.normalizer', [
            'priority' => 1,
        ]);

    $services->set(App\Normalizer\DeviceNormalizer::class)
        ->tag('serializer.normalizer', [
            'priority' => 10,
        ]);

    $services->set(App\Normalizer\JecouteRegionNormalizer::class)
        ->tag('serializer.normalizer', [
            'priority' => 1,
        ]);

    $services->set(App\Normalizer\ConstraintViolationListNormalizer::class)
        ->decorate('api_platform.problem.normalizer.validation_exception')
        ->tag('serializer.normalizer', [
            'priority' => -800,
        ])
        ->args([
            service('serializer.name_converter.metadata_aware'),
        ]);

    $services->set(App\Normalizer\Indexer\ThrowExceptionNormalizer::class)
        ->tag('serializer.normalizer', [
            'priority' => -801,
        ]);

    $services->set(App\Scope\GeneralScopeGenerator::class)
        ->arg('$generators', tagged_iterator('app.adherent.scope_generator'));

    $services->set(App\Algolia\SearchService::class)
        ->decorate(Algolia\SearchBundle\SearchService::class)
        ->args([
            service('.inner'),
            '%kernel.debug%',
        ]);

    $services->alias(Geocoder\Geocoder::class, 'bazinga_geocoder.geocoder');

    $services->set(App\Recaptcha\RecaptchaApiClient::class)
        ->arg('$projectId', '%env(GCP_PROJECT_ID)%')
        ->arg('$defaultSiteKey', '%env(RECAPTCHA_PUBLIC_KEY)%');

    $services->set(App\Recaptcha\FriendlyCaptchaApiClient::class)
        ->arg('$privateKey', '%env(FRIENDLY_CAPTCHA_PRIVATE_KEY)%')
        ->arg('$defaultSiteKey', '%env(FRIENDLY_CAPTCHA_DEFAULT_SITE_KEY)%');

    $services->set(App\Validator\RecaptchaValidator::class)
        ->args([
            tagged_iterator('app.recaptcha_api_client'),
        ]);

    $services->alias(Lexik\Bundle\PayboxBundle\Paybox\System\Cancellation\Request::class, 'lexik_paybox.request_cancellation_handler');

    $services->alias(Lexik\Bundle\PayboxBundle\Paybox\System\Base\Request::class, 'lexik_paybox.request_handler');

    $services->set('app.paybox.membership_handler', Lexik\Bundle\PayboxBundle\Paybox\System\Base\Request::class)
        ->arg('$parameters', [
            'production' => '%env(bool:PAYBOX_PRODUCTION)%',
            'site' => '%env(PAYBOX_MEMBERSHIP_SITE)%',
            'rank' => '%env(PAYBOX_MEMBERSHIP_RANK)%',
            'login' => '%env(PAYBOX_MEMBERSHIP_IDENTIFIER)%',
            'currencies' => [
                '978',
            ],
            'hmac' => [
                'key' => '%env(PAYBOX_MEMBERSHIP_KEY)%',
                'algorithm' => 'sha512',
                'signature_name' => 'Sign',
            ],
        ])
        ->arg('$servers', '%lexik_paybox.servers%')
        ->arg('$factory', service('form.factory'));

    $services->set(App\Donation\Paybox\PayboxFormFactory::class)
        ->arg('$membershipRequestHandler', service('app.paybox.membership_handler'));

    $services->alias(League\OAuth2\Server\AuthorizationValidators\AuthorizationValidatorInterface::class, App\OAuth\AuthorizationValidators\JsonWebTokenValidator::class);

    $services->alias(League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface::class, App\OAuth\Store\AccessTokenStore::class);

    $services->alias(League\OAuth2\Server\Repositories\UserRepositoryInterface::class, App\OAuth\Repository\OAuthUserRepository::class);

    $services->alias(League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface::class, App\OAuth\Store\AuthorizationCodeStore::class);

    $services->alias(League\OAuth2\Server\Repositories\ClientRepositoryInterface::class, App\OAuth\Store\ClientStore::class);

    $services->alias(League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface::class, App\OAuth\Store\RefreshTokenStore::class);

    $services->alias(League\OAuth2\Server\Repositories\ScopeRepositoryInterface::class, App\OAuth\Store\ScopeStore::class);

    $services->set(League\OAuth2\Server\AuthorizationServer::class)
        ->factory([
            service(App\OAuth\AuthorizationServerFactory::class),
            'createServer',
        ]);

    $services->set(League\OAuth2\Server\ResourceServer::class)
        ->arg('$publicKey', service('app.ssl_public_key'));

    $services->alias(Sonata\AdminBundle\Builder\ShowBuilderInterface::class, 'sonata.admin.builder.orm_show');

    $services->alias(Sonata\Exporter\Exporter::class, 'sonata.exporter.exporter');

    $services->set(Symfony\Component\HttpFoundation\Session\Storage\Handler\RedisSessionHandler::class)
        ->args([
            service('cache.default_redis_provider'),
            [
                'ttl' => '%env(SESSION_TTL)%',
                'prefix' => 'session:',
            ],
        ]);

    $services->set(App\VotingPlatform\AdherentMandate\AdherentMandateFactory::class)
        ->args([
            tagged_iterator('app.voting_platform.mandate_factory'),
        ]);

    $services->set(App\VotingPlatform\Election\ResultCalculator::class)
        ->args([
            tagged_iterator(tag: 'app.voting_platform.result_calculator', defaultPriorityMethod: 'getPriority'),
        ]);

    $services->set(App\JMEFilter\FiltersGenerator::class)
        ->args([
            tagged_iterator('app.filter.builder'),
        ]);

    $services->set(App\Firebase\JeMarcheMessaging::class)
        ->lazy(true);

    $services->set(App\Api\Provider\EventsFallbackProvider::class)
        ->args([
            service('api_platform.doctrine.orm.state.collection_provider'),
        ]);

    $services->set(App\Api\Serializer\ReferralGroupsContextBuilder::class)
        ->decorate('api_platform.serializer.context_builder')
        ->args([
            service('.inner'),
        ]);

    $services->set(App\Api\Serializer\RiposteGroupsContextBuilder::class)
        ->decorate('api_platform.serializer.context_builder')
        ->args([
            service('.inner'),
        ]);

    $services->set(App\Api\Serializer\JecouteNewsGroupsContextBuilder::class)
        ->decorate('api_platform.serializer.context_builder')
        ->args([
            service('.inner'),
        ]);

    $services->set(App\Api\Serializer\EnforceTypeValidationContextBuilder::class)
        ->decorate('api_platform.serializer.context_builder')
        ->args([
            service('.inner'),
        ]);

    $services->set(App\Api\Serializer\PrivatePublicContextBuilder::class)
        ->decorate('api_platform.serializer.context_builder')
        ->args([
            service('.inner'),
        ]);

    $services->set(App\Api\Serializer\CommitteeGroupsContextBuilder::class)
        ->decorate('api_platform.serializer.context_builder')
        ->args([
            service('.inner'),
        ]);

    $services->set(App\Api\Serializer\DesignationGroupsContextBuilder::class)
        ->decorate('api_platform.serializer.context_builder')
        ->args([
            service('.inner'),
        ]);

    $services->set(App\Api\Serializer\EventGroupsContextBuilder::class)
        ->decorate('api_platform.serializer.context_builder')
        ->args([
            service('.inner'),
        ]);

    $services->set(Symfony\Bridge\Monolog\Processor\TokenProcessor::class)
        ->tag('monolog.processor');

    $services->set(Symfony\Bridge\Monolog\Processor\SwitchUserTokenProcessor::class)
        ->tag('monolog.processor');

    $services->set(App\Normalizer\DateTimeNormalizer::class)
        ->decorate('serializer.normalizer.datetime')
        ->args([
            service('.inner'),
        ]);

    $services->set(App\Api\IriConverterDecorator::class)
        ->decorate('api_platform.symfony.iri_converter')
        ->args([
            service('.inner'),
        ]);

    $services->alias(App\FranceCities\CitiesStorageInterface::class, App\FranceCities\CitiesStorage::class);

    $services->set(App\FranceCities\CachedCitiesStorage::class)
        ->decorate(App\FranceCities\CitiesStorage::class)
        ->arg('$decorated', service('.inner'))
        ->arg('$cache', inline_service(Symfony\Component\Cache\Psr16Cache::class)->args([service('cache.app')]))
    ;

    $services->set('app.simple_cache.event_notifications', Symfony\Component\Cache\Psr16Cache::class)
        ->args([
            service('app.cache.event_notifications'),
        ]);

    $services->set(App\Event\Handler\EventLiveBeginEmailChunkNotificationCommandHandler::class)
        ->arg('$cache', service('app.simple_cache.event_notifications'));

    $services->set(App\Event\Handler\EventLiveBeginPushChunkNotificationCommandHandler::class)
        ->arg('$cache', service('app.simple_cache.event_notifications'));

    $services->alias('logger', 'monolog.logger')
        ->public();

    $services->set(App\Mailer\SenderMessageMapper::class)
        ->args([
            [
                App\Mailer\Message\Renaissance\RenaissanceMessageInterface::class => [
                    'name' => 'Renaissance',
                    'email' => '%env(RENAISSANCE_SENDER_EMAIL)%',
                ],
            ],
        ]);

    $services->set(App\Donation\Request\DonationRequestUtils::class)
        ->arg('$tokenManager', service(App\Security\SimpleCsrfTokenManager::class));

    $services->set(App\Mailer\EmailTemplateFactory::class)
        ->arg('$senderEmail', '%transactional_sender_email%')
        ->arg('$senderName', '%transactional_sender_name%');

    $services->set(App\Mandrill\EmailClient::class)
        ->arg('$apiKey', '%env(MANDRILL_API_KEY)%')
        ->arg('$testApiKey', '%env(MANDRILL_TEST_API_KEY)%');

    $services->set(League\Glide\Responses\SymfonyResponseFactory::class);

    $services->set(League\Glide\Server::class)
        ->factory([
            League\Glide\ServerFactory::class,
            'create',
        ])
        ->args([
            [
                'source' => service(League\Flysystem\FilesystemOperator::class),
                'cache' => '%kernel.cache_dir%',
                'response' => service(League\Glide\Responses\SymfonyResponseFactory::class),
                'max_image_size' => 4000000,
            ],
        ]);

    $services->set(App\Security\Http\LoginLink\LoginLinkHandler::class)
        ->decorate('security.authenticator.login_link_handler.main')
        ->args([
            service('.inner'),
        ]);

    $services->set(App\Security\ApiAuthenticationEntryPoint::class)
        ->args([
            service('security.authenticator.form_login.main'),
        ]);

    $services->alias(Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface::class, App\Security\Http\LoginLink\LoginLinkHandler::class);

    $services->set(App\Security\Http\LoginLink\Authentication\DefaultAuthenticationSuccessHandler::class)
        ->decorate('security.authentication.success_handler.main.login_link')
        ->args([
            service('.inner'),
        ]);

    $services->set(League\CommonMark\CommonMarkConverter::class);

    $services->set(App\Search\SearchParametersFilter::class)
        ->share(false)
        ->args([
            service(App\Geocoder\Geocoder::class),
            service('cache.app'),
        ])
        ->call('setMaxResults', [
            '%search_max_results%',
        ]);

    $services->set(App\Search\SearchResultsProvidersManager::class)
        ->call('addProvider', [
            service(App\Search\CommitteeSearchResultsProvider::class),
        ])
        ->call('addProvider', [
            service(App\Search\EventSearchResultsProvider::class),
        ]);

    $services->set(App\Api\Doctrine\JecouteNewsExtension::class)
        ->tag('api_platform.doctrine.orm.query_extension.collection', [
            'priority' => 9,
        ]);

    $services->set(App\Api\Doctrine\EventExtension::class)
        ->tag('api_platform.doctrine.orm.query_extension.collection', [
            'priority' => 9,
        ]);

    $services->set(App\Api\Doctrine\EventOrderExtension::class)
        ->tag('api_platform.doctrine.orm.query_extension.collection', [
            'priority' => -35,
        ]);

    $services->set(App\Api\Doctrine\GeoZoneExtension::class)
        ->tag('api_platform.doctrine.orm.query_extension.collection', [
            'priority' => 9,
        ]);

    $services->set(App\Api\Doctrine\AdherentMessageCollectionExtension::class)
        ->tag('api_platform.doctrine.orm.query_extension.collection', [
            'priority' => 9,
        ]);

    $services->set(App\Api\Doctrine\LoadActivePapCampaignExtension::class)
        ->tag('api_platform.doctrine.orm.query_extension.collection', [
            'priority' => 9,
        ]);

    $services->set(App\Api\Doctrine\EmailTemplateExtension::class)
        ->tag('api_platform.doctrine.orm.query_extension.collection', [
            'priority' => 9,
        ]);

    $services->set(App\Api\Doctrine\ReferralExtension::class)
        ->tag('api_platform.doctrine.orm.query_extension.collection', [
            'priority' => 9,
        ]);

    $services->set(App\Entity\Listener\ContainingUserDocumentListener::class)
        ->public()
        ->args([
            service(App\UserDocument\UserDocumentManager::class),
            '%pattern_uuid%',
        ])
        ->tag('doctrine.orm.entity_listener', [
            'entity' => App\Entity\Jecoute\News::class,
            'event' => 'prePersist',
        ])
        ->tag('doctrine.orm.entity_listener', [
            'entity' => App\Entity\Jecoute\News::class,
            'event' => 'preUpdate',
        ])
        ->tag('doctrine.orm.entity_listener', [
            'entity' => App\Entity\Jecoute\News::class,
            'event' => 'postUpdate',
        ])
        ->tag('doctrine.orm.entity_listener', [
            'entity' => App\Entity\Jecoute\News::class,
            'event' => 'preRemove',
        ])
        ->tag('doctrine.orm.entity_listener', [
            'entity' => App\Entity\Jecoute\News::class,
            'event' => 'postRemove',
        ]);

    $services->alias(Vich\UploaderBundle\Templating\Helper\UploaderHelperInterface::class, Vich\UploaderBundle\Templating\Helper\UploaderHelper::class);
};
