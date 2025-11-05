<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $configurator) {
    $services = $configurator->services()->defaults()
        ->autowire()
        ->autoconfigure()
    ;

    $services
        ->load('App\Admin\Filter\\', '../../src/Admin/Filter')
        ->tag('sonata.admin.filter.type')
    ;

    $services->alias(Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter::class, 'sonata.admin.orm.filter.type.callback');

    $services->set(App\Admin\AdministratorFactory::class);
    $services->set(App\Admin\AdministratorRoleHistoryHandler::class);
    $services->set(App\Admin\Exporter\IteratorCallbackDataSource::class);
    $services
        ->set(App\Admin\Exporter\DataSourceDecorator::class)
        ->decorate('sonata.admin.data_source.orm')
        ->args([service('.inner')])
    ;
    $services
        ->set('app.admin.adherent', App\Admin\AdherentAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\Adherent::class, 'controller' => App\Controller\Admin\AdminAdherentCRUDController::class, 'label' => 'Militants', 'group' => 'Militants', 'default' => true])
    ;
    $services
        ->set('app.admin.app_session', App\Admin\AppSessionAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\AppSession::class, 'label' => 'Sessions', 'group' => 'Militants'])
        ->call('setTemplate', ['list', 'admin/app_session/list.html.twig'])
    ;
    $services
        ->set('app.admin.unregistration', App\Admin\UnregistrationAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\Unregistration::class, 'label' => 'Désadhésions/exclusions', 'group' => 'Militants'])
        ->call('setTemplate', ['list', 'admin/adherent/unregistration_list.html.twig'])
    ;
    $services
        ->set('app.admin.certification_request', App\Admin\CertificationRequestAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\CertificationRequest::class, 'controller' => App\Controller\Admin\AdminCertificationRequestController::class, 'label' => 'Demandes de certification', 'group' => 'Militants'])
        ->call('setTemplate', ['show', 'admin/certification_request/show.html.twig'])
    ;
    $services
        ->set('app.admin.reporting.adherent_certification_history', App\Admin\Reporting\AdherentCertificationHistoryAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\Reporting\AdherentCertificationHistory::class, 'label' => 'Historique de certification', 'group' => 'Militants'])
    ;
    $services
        ->set('app.admin.reporting.declared_mandate_history', App\Admin\Reporting\DeclaredMandateHistoryAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\Reporting\DeclaredMandateHistory::class, 'label' => 'Historique mandats déclarés', 'group' => 'Militants'])
    ;
    $services
        ->set('app.admin.user_action_history', App\Admin\UserActionHistoryAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\UserActionHistory::class, 'label' => 'Historique adhérents', 'group' => 'Militants'])
    ;
    $services
        ->set('app.admin.invite', App\Admin\InviteAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\Invite::class, 'label' => 'Invitations', 'group' => 'Militants'])
    ;
    $services
        ->set('app.admin.referral', App\Admin\ReferralAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\Referral::class, 'label' => 'Parrainages', 'group' => 'Militants'])
    ;
    $services
        ->set('app.admin.adherent_referrer', App\Admin\AdherentReferrerAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\Adherent::class, 'label' => 'Parrains', 'group' => 'Militants'])
    ;
    $services
        ->set('app.admin.adherent_request', App\Admin\AdherentRequestAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\Renaissance\Adhesion\AdherentRequest::class, 'label' => 'Demandes d\'adhésion', 'group' => 'Militants'])
    ;
    $services
        ->set('app.admin.cms_block', App\Admin\CmsBlockAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\CmsBlock::class, 'label' => 'Blocs statiques', 'group' => 'Communication'])
    ;
    $services
        ->set('app.admin.redirection', App\Admin\RedirectionAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\Redirection::class, 'label' => 'Redirections', 'group' => 'Communication'])
    ;
    $services
        ->set('app.admin.newsletter_subscription', App\Admin\NewsletterSubscriptionAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\NewsletterSubscription::class, 'label' => 'Newsletter', 'group' => 'Communication'])
    ;
    $services
        ->set('app.admin.qr_code', App\Admin\QrCodeAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\QrCode::class, 'label' => 'QR Codes', 'group' => 'Communication'])
    ;
    $services
        ->set('app.admin.chatbot_chatbot', App\Admin\Chatbot\ChatbotAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\Chatbot\Chatbot::class, 'label' => 'Chatbots', 'group' => 'Communication'])
    ;
    $services
        ->set('app.admin.email_template', App\Admin\Email\EmailTemplateAdmin::class)
        ->args(['%env(int:EMAIL_TEMPLATE_UNLAYER_TEMPLATE_ID)%'])
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\Email\EmailTemplate::class, 'label' => 'Modèle d\'email JME', 'group' => 'Communication'])
    ;
    $services
        ->set('app.admin.national_event', App\Admin\NationalEvent\NationalEventAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\NationalEvent\NationalEvent::class, 'controller' => App\Controller\Admin\AdminNationalEventCRUDController::class, 'label' => 'Meetings', 'group' => 'Meetings'])
    ;
    $services
        ->set('app.admin.national_event_inscriptions', App\Admin\NationalEvent\NationalEventInscriptionsAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\NationalEvent\EventInscription::class, 'controller' => App\Controller\Admin\AdminNationalEventInscriptionCRUDController::class, 'label' => 'Inscrits', 'group' => 'Meetings'])
    ;
    $services
        ->set('app.admin.national_event_inscription_payments', App\Admin\NationalEvent\PaymentAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\NationalEvent\Payment::class, 'label' => 'Paiements', 'group' => 'Meetings'])
    ;
    $services
        ->set('app.admin.national_event_scan', App\Admin\NationalEvent\NationalEventScanAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\NationalEvent\TicketScan::class, 'label' => 'Scans', 'group' => 'Meetings'])
    ;
    $services
        ->set('app.admin.committee', App\Admin\CommitteeAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\Committee::class, 'controller' => App\Controller\Admin\AdminCommitteeCRUDController::class, 'label' => 'Comités', 'group' => 'Territoires'])
        ->call('setTemplate', ['show', 'admin/committee/show.html.twig'])
        ->call('setTemplate', ['edit', 'admin/committee/edit.html.twig'])
    ;
    $services
        ->set('app.admin.committee_membership', App\Admin\CommitteeMembershipAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\CommitteeMembership::class, 'show_in_dashboard' => false])
    ;
    $services
        ->set('app.admin.live_stream', App\Admin\LiveStreamAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\LiveStream::class, 'label' => 'Live Stream', 'group' => 'Territoires'])
    ;
    $services
        ->set('app.admin.event', App\Admin\EventAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\Event\Event::class, 'label' => 'Événements', 'group' => 'Territoires'])
        ->call('setTemplate', ['show', 'admin/event/show.html.twig'])
        ->call('setTemplate', ['edit', 'admin/event/edit.html.twig'])
    ;
    $services
        ->set('app.admin.event_category', App\Admin\EventCategoryAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\Event\EventCategory::class, 'label' => 'Catégories d\'événements', 'group' => 'Territoires'])
    ;
    $services
        ->set('app.admin.event_group_category', App\Admin\EventGroupCategoryAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\Event\EventGroupCategory::class, 'label' => 'Groupe de Catégories d\'événements', 'group' => 'Territoires'])
    ;
    $services
        ->set('app.admin.event_registration', App\Admin\EventRegistrationAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\Event\EventRegistration::class, 'label' => 'Inscriptions aux événements', 'group' => 'Territoires'])
    ;
    $services
        ->set('app.admin.team.team', App\Admin\Team\TeamAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\Team\Team::class, 'label' => 'Équipes', 'group' => 'Territoires'])
    ;
    $services
        ->set('app.admin.team.member', App\Admin\Team\MemberAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\Team\Member::class, 'show_in_dashboard' => false])
    ;
    $services
        ->set('app.admin.reporting.team_member_history', App\Admin\Reporting\TeamMemberHistoryAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\Reporting\TeamMemberHistory::class, 'label' => 'Historique des équipes', 'group' => 'Territoires'])
    ;
    $services
        ->set('app.admin.consultation', App\Admin\ConsultationAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\Consultation::class, 'label' => 'Consultations', 'group' => 'Territoires'])
    ;
    $services
        ->set('app.admin.file', App\Admin\Filesystem\FileAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\Filesystem\File::class, 'label' => 'Documents', 'group' => 'Territoires'])
    ;
    $services
        ->set('app.admin.document', App\Admin\DocumentAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\Document::class, 'label' => 'Documents JME', 'group' => 'Territoires'])
    ;
    $services
        ->set('app.admin.general_meeting_report', App\Admin\GeneralMeetingReportAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\GeneralMeeting\GeneralMeetingReport::class, 'label' => 'Centre d\'archives JME', 'group' => 'Territoires'])
    ;
    $services
        ->set('app.admin.hub_item', App\Admin\HubItemAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\HubItem::class, 'label' => 'Hub candidat', 'group' => 'Élections'])
    ;
    $services
        ->set('app.admin.report', App\Admin\ReportAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\Report\Report::class, 'label' => 'Signalements', 'group' => 'Territoires'])
    ;
    $services->alias(App\Admin\ReportAdmin::class, 'app.admin.report');
    $services
        ->set('app.admin.agora', App\Admin\AgoraAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\Agora::class, 'label' => 'Agoras', 'group' => 'Territoires'])
    ;
    $services
        ->set('app.admin.adherent_message', App\Admin\AdherentMessageAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\AdherentMessage\AdherentMessage::class, 'controller' => App\Controller\Admin\AdminAdherentMessageCRUDController::class, 'label' => 'Publications locales', 'group' => 'Territoires'])
    ;
    $services
        ->set('app.admin.agora_membership', App\Admin\AgoraMembershipAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\AgoraMembership::class, 'show_in_dashboard' => false])
    ;
    $services
        ->set('app.admin.general_convention', App\Admin\GeneralConventionAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\GeneralConvention\GeneralConvention::class, 'label' => 'États généraux', 'group' => 'Idées'])
    ;
    $services
        ->set('app.admin.adherent_formation', App\Admin\FormationAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\AdherentFormation\Formation::class, 'label' => 'Formations', 'group' => 'Mobilisation'])
        ->call('setTemplate', ['edit', 'admin/adherent_formation/edit.html.twig'])
    ;
    $services
        ->set('app.admin.phoning.campaign', App\Admin\Phoning\CampaignAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\Phoning\Campaign::class, 'label' => 'Phoning » Campagnes', 'group' => 'Mobilisation'])
    ;
    $services
        ->set('app.admin.phoning.campaign_history', App\Admin\Phoning\CampaignHistoryAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\Phoning\CampaignHistory::class, 'label' => 'Phoning » Appels', 'group' => 'Mobilisation'])
    ;
    $services
        ->set('app.admin.pap.campaign', App\Admin\Pap\CampaignAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\Pap\Campaign::class, 'label' => 'PAP » Campagnes', 'group' => 'Mobilisation'])
    ;
    $services
        ->set('app.admin.pap.campaign_history', App\Admin\Pap\CampaignHistoryAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\Pap\CampaignHistory::class, 'label' => 'PAP » Portes frappées', 'group' => 'Mobilisation'])
    ;
    $services
        ->set('app.admin.adherent_elected_representative', App\Admin\AdherentElectedRepresentativeAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\Adherent::class, 'controller' => App\Controller\Admin\AdminAdherentCRUDController::class, 'label' => 'Élus', 'group' => 'Élus'])
    ;
    $services
        ->set('app.admin.republican_silence', App\Admin\RepublicanSilenceAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\RepublicanSilence::class, 'label' => 'Silence républicain', 'group' => 'Élections'])
    ;
    $services
        ->set('app.admin.procuration_v2.election', App\Admin\Procuration\ElectionAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\ProcurationV2\Election::class, 'label' => 'Procurations » Élections', 'group' => 'Élections'])
    ;
    $services
        ->set('app.admin.procuration_v2.procuration_request', App\Admin\Procuration\ProcurationRequestAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\ProcurationV2\ProcurationRequest::class, 'label' => 'Procurations » Demandes incomplètes', 'group' => 'Élections'])
    ;
    $services
        ->set('app.admin.procuration_v2.request', App\Admin\Procuration\RequestAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\ProcurationV2\Request::class, 'label' => 'Procurations » Mandants', 'group' => 'Élections'])
    ;
    $services
        ->set('app.admin.procuration_v2.proxy', App\Admin\Procuration\ProxyAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\ProcurationV2\Proxy::class, 'label' => 'Procurations » Mandataires', 'group' => 'Élections'])
    ;
    $services
        ->set('app.admin.election', App\Admin\ElectionAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\Election::class, 'label' => 'Assesseurs » Élections', 'group' => 'Élections'])
    ;
    $services
        ->set('app.admin.voting_platform.designation', App\Admin\VotingPlatform\Designation\DesignationAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\VotingPlatform\Designation\Designation::class, 'label' => 'Désignations statutaires', 'group' => 'Élections internes'])
    ;
    $services
        ->set('app.admin.voting_platform.designation_poll', App\Admin\VotingPlatform\Designation\Poll\DesignationPollAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\VotingPlatform\Designation\Poll\Poll::class, 'label' => 'Designation » Questionnaires', 'group' => 'Élections internes'])
    ;
    $services
        ->set('app.admin.voting_platform.designation_poll_question_choice', App\Admin\VotingPlatform\Designation\Poll\PollQuestionAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\VotingPlatform\Designation\Poll\PollQuestion::class, 'show_in_dashboard' => false])
    ;
    $services
        ->set('app.admin.voting_platform.designation_candidacy_pool', App\Admin\VotingPlatform\Designation\CandidacyPool\CandidacyPoolAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\VotingPlatform\Designation\CandidacyPool\CandidacyPool::class, 'label' => 'Designation » Candidatures', 'group' => 'Élections internes'])
    ;
    $services
        ->set('app.admin.voting_platform.designation_candidacy_pool_candidacies_groups', App\Admin\VotingPlatform\Designation\CandidacyPool\CandidaciesGroupAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\VotingPlatform\Designation\CandidacyPool\CandidaciesGroup::class, 'show_in_dashboard' => false])
    ;
    $services
        ->set('app.admin.voting_platform.designation_candidacy_pool_candidacy', App\Admin\VotingPlatform\Designation\CandidacyPool\CandidacyAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\VotingPlatform\Designation\CandidacyPool\Candidacy::class, 'show_in_dashboard' => false])
    ;
    $services
        ->set('app.admin.designation.election', App\Admin\Designation\VotingPlatformElectionAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\VotingPlatform\Election::class, 'label' => 'Elections', 'group' => 'Élections internes'])
        ->call('setTemplate', ['show', 'admin/instances/election_dashboard.html.twig'])
    ;
    $services
        ->set('app.admin.designation.vote', App\Admin\Designation\VotingPlatformVoteAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\VotingPlatform\Vote::class, 'label' => 'Émargements', 'group' => 'Élections internes'])
    ;
    $services
        ->set('app.admin.local_election', App\Admin\LocalElection\LocalElectionAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\LocalElection\LocalElection::class, 'label' => 'Départementales » Accueil', 'group' => 'Élections internes'])
    ;
    $services
        ->set('app.admin.local_election.candidacies_group', App\Admin\LocalElection\CandidaciesGroupAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\LocalElection\CandidaciesGroup::class, 'controller' => App\Controller\Admin\AdminCandidaciesGroupCandidateImportController::class, 'label' => 'Départementales » Listes', 'group' => 'Élections internes'])
    ;
    $services
        ->set('app.admin.local_election.candidacy', App\Admin\LocalElection\CandidacyAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\LocalElection\Candidacy::class, 'show_in_dashboard' => false])
    ;
    $services
        ->set('app.admin.local_election.substitute_candidacy', App\Admin\LocalElection\SubstituteCandidacyAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\LocalElection\SubstituteCandidacy::class, 'show_in_dashboard' => false])
    ;
    $services
        ->set('app.admin.alert', App\Admin\AppAlertAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\AppAlert::class, 'label' => 'Alertes', 'group' => 'App mobile'])
    ;
    $services
        ->set('app.admin.private_message', App\Admin\TimelinePrivateMessageAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\TimelineItemPrivateMessage::class, 'label' => 'Messages privés (timeline)', 'group' => 'App mobile'])
    ;
    $services
        ->set('app.admin.jecoute.news', App\Admin\Jecoute\NewsAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\Jecoute\News::class, 'label' => 'Notifications', 'group' => 'App mobile'])
        ->call('setTemplate', ['edit', 'admin/jecoute/news/edit.html.twig'])
    ;
    $services
        ->set('app.admin.jecoute.national_region', App\Admin\Jecoute\NationalRegionAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\Jecoute\Region::class, 'label' => 'Actu principale nationale', 'group' => 'App mobile'])
    ;
    $services
        ->set('app.admin.jecoute.candidate_region', App\Admin\Jecoute\CandidateRegionAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\Jecoute\Region::class, 'label' => 'Actu principale régionale', 'group' => 'App mobile'])
    ;
    $services
        ->set('app.admin.jecoute.referent_region', App\Admin\Jecoute\ReferentRegionAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\Jecoute\Region::class, 'label' => 'Actu principale départementale', 'group' => 'App mobile'])
    ;
    $services
        ->set('app.admin.jecoute_suggested_question', App\Admin\JecouteSuggestedQuestionAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\Jecoute\SuggestedQuestion::class, 'label' => 'Questions paniers', 'group' => 'App mobile'])
        ->call('setTemplate', ['edit', 'admin/jecoute/suggested_question_edit.html.twig'])
    ;
    $services
        ->set('app.admin.jecoute_local_survey', App\Admin\JecouteLocalSurveyAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\Jecoute\LocalSurvey::class, 'label' => 'Questionnaires locaux', 'group' => 'App mobile'])
        ->call('setTemplate', ['edit', 'admin/jecoute/survey_edit.html.twig'])
    ;
    $services
        ->set('app.admin.jecoute_national_survey', App\Admin\JecouteNationalSurveyAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\Jecoute\NationalSurvey::class, 'label' => 'Questionnaires nationaux', 'group' => 'App mobile'])
        ->call('setTemplate', ['edit', 'admin/jecoute/survey_edit.html.twig'])
    ;
    $services
        ->set('app.admin.jecoute_data_survey', App\Admin\DataSurveyAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\Jecoute\DataSurvey::class, 'show_in_dashboard' => false])
    ;
    $services
        ->set('app.admin.poll.poll', App\Admin\Poll\NationalPollAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\Poll\NationalPoll::class, 'label' => 'Sondages', 'group' => 'App mobile'])
        ->call('setTemplate', ['show', 'admin/poll/show.html.twig'])
    ;
    $services
        ->set('app.admin.jecoute.riposte', App\Admin\Jecoute\RiposteAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\Jecoute\Riposte::class, 'label' => 'Ripostes', 'group' => 'App mobile'])
    ;
    $services
        ->set('app.admin.jecoute.resource.link', App\Admin\Jecoute\ResourceLinkAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\Jecoute\ResourceLink::class, 'label' => 'Ressources', 'group' => 'App mobile'])
    ;
    $services
        ->set('app.admin.je_mengage.header_block', App\Admin\JeMengage\HeaderBlockAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\JeMengage\HeaderBlock::class, 'label' => 'Paramètres d\'en-tête', 'group' => 'App mobile'])
    ;
    $services
        ->set('app.admin.donation', App\Admin\DonationAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\Donation::class, 'label' => 'Dons', 'group' => 'Finances'])
    ;
    $services
        ->set('app.admin.donator', App\Admin\DonatorAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\Donator::class, 'label' => 'Donateurs', 'group' => 'Finances'])
        ->call('setTemplate', ['edit', 'admin/donator/edit.html.twig'])
    ;
    $services
        ->set('app.admin.donator_tag', App\Admin\DonatorTagAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\DonatorTag::class, 'label' => 'Tags donateurs', 'group' => 'Finances'])
    ;
    $services
        ->set('app.admin.donation_tag', App\Admin\DonationTagAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\DonationTag::class, 'label' => 'Tags dons', 'group' => 'Finances'])
    ;
    $services
        ->set('app.admin.ohme_contact', App\Admin\Ohme\ContactAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\Ohme\Contact::class, 'label' => 'Ohme » Contacts', 'group' => 'Finances'])
    ;
    $services
        ->set('app.admin.administrator', App\Admin\AdministratorAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\Administrator::class, 'label' => 'Administrateurs', 'group' => 'Tech'])
    ;
    $services
        ->set('app.admin.administrator_role', App\Admin\AdministratorRoleAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\AdministratorRole::class, 'label' => 'Rôles Administrateur', 'group' => 'Tech'])
    ;
    $services
        ->set('app.admin.administrator_action_history', App\Admin\AdministratorActionHistoryAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\AdministratorActionHistory::class, 'label' => 'Historique administrateurs', 'group' => 'Tech'])
    ;
    $services
        ->set('app.admin.email', App\Admin\Email\EmailLogAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\Email\EmailLog::class, 'controller' => App\Controller\Admin\AdminEmailCRUDController::class, 'label' => 'Logs Mailer', 'group' => 'Tech'])
    ;
    $services
        ->set('app.admin.push_notification', App\Admin\PushNotificationAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\Notification::class, 'label' => 'Logs Push', 'group' => 'Tech'])
    ;
    $services
        ->set('app.admin.transactional_email_template', App\Admin\Email\TransactionalEmailTemplateAdmin::class)
        ->args(['%env(APP_ENVIRONMENT)%'])
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\Email\TransactionalEmailTemplate::class, 'controller' => App\Controller\Admin\AdminEmailCRUDController::class, 'label' => 'Templates Email', 'group' => 'Tech'])
    ;
    $services
        ->set('app.admin.subscription_type', App\Admin\SubscriptionTypeAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\SubscriptionType::class, 'label' => 'Préférence de notifications', 'group' => 'Tech'])
    ;
    $services
        ->set('app.admin.uploadable_file', App\Admin\UploadableFileAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\UploadableFile::class, 'show_in_dashboard' => false])
    ;
    $services
        ->set('app.admin.client', App\Admin\OAuth\ClientAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\OAuth\Client::class, 'label' => 'Client', 'group' => 'Tech'])
    ;
    $services
        ->set('app.admin.scope', App\Admin\ScopeAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\Scope::class, 'label' => 'Scopes', 'group' => 'Tech'])
    ;
    $services
        ->set('app.admin.adherent_static_label', App\Admin\AdherentStaticLabelAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\AdherentStaticLabel::class, 'label' => 'Labels statiques', 'group' => 'Tech'])
    ;
    $services
        ->set('app.admin.adherent_static_label_category', App\Admin\AdherentStaticLabelCategoryAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\AdherentStaticLabelCategory::class, 'label' => 'Catégories de label statique', 'group' => 'Tech'])
    ;
    $services
        ->set('app.admin.geo.zone', App\Admin\Geo\ZoneAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\Geo\Zone::class, 'show_in_dashboard' => false])
    ;
    $services
        ->set('app.admin.jecoute.jecoute_managed_area_admin', App\Admin\Jecoute\JecouteManagedAreaAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\JecouteManagedArea::class, 'show_in_dashboard' => false])
    ;
    $services
        ->set('app.admin.adherent_zone_based_role_admin', App\Admin\AdherentZoneBasedRoleAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\AdherentZoneBasedRole::class, 'show_in_dashboard' => false])
    ;
    $services
        ->set('app.admin.elected_representative_adherent_mandate', App\Admin\ElectedRepresentativeAdherentMandateAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\AdherentMandate\ElectedRepresentativeAdherentMandate::class, 'show_in_dashboard' => false])
    ;
    $services
        ->set('app.admin.audience', App\Admin\Audience\AudienceAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\Audience\Audience::class, 'show_in_dashboard' => false])
    ;
    $services
        ->set('app.admin.elected_representative', App\Admin\ElectedRepresentative\ElectedRepresentativeAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\ElectedRepresentative\ElectedRepresentative::class, 'label' => 'Registre des élus', 'group' => 'Archives (à garder)'])
        ->call('setTemplate', ['edit', 'admin/elected_representative/edit.html.twig'])
    ;
    $services
        ->set('app.admin.elected_representative.social_network_link', App\Admin\ElectedRepresentative\SocialNetworkLinkAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\ElectedRepresentative\SocialNetworkLink::class, 'show_in_dashboard' => false])
    ;
    $services
        ->set('app.admin.elected_representative.label', App\Admin\ElectedRepresentative\ElectedRepresentativeLabelAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\ElectedRepresentative\ElectedRepresentativeLabel::class, 'show_in_dashboard' => false])
    ;
    $services
        ->set('app.admin.elected_representative.mandate', App\Admin\ElectedRepresentative\MandateAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\ElectedRepresentative\Mandate::class, 'show_in_dashboard' => false])
    ;
    $services
        ->set('app.admin.elected_representative.political_function', App\Admin\ElectedRepresentative\PoliticalFunctionAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\ElectedRepresentative\PoliticalFunction::class, 'show_in_dashboard' => false])
    ;
    $services
        ->set('app.admin.elected_representative.zone', App\Admin\ElectedRepresentative\ZoneAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\ElectedRepresentative\Zone::class, 'show_in_dashboard' => false])
    ;
    $services
        ->set('app.admin.renaissance_department_site', App\Admin\DepartmentSite\DepartmentSiteAdmin::class)
        ->args(['%env(int:DPT_SITE_UNLAYER_TEMPLATE_ID)%'])
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\DepartmentSite\DepartmentSite::class, 'label' => 'Sites Départementaux', 'group' => 'Archives (à garder)'])
    ;
    $services
        ->set('app.admin.proposal_theme', App\Admin\ProposalThemeAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\ProposalTheme::class, 'label' => 'Programme » Thèmes', 'group' => 'Archives (à garder)'])
    ;
    $services
        ->set('app.admin.proposal', App\Admin\ProposalAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\Proposal::class, 'label' => 'Programme » Propositions', 'group' => 'Archives (à garder)'])
        ->call('setTemplate', ['outer_list_rows_mosaic', 'admin/media/mosaic.html.twig'])
    ;
    $services
        ->set('app.admin.media', App\Admin\MediaAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\Media::class, 'label' => 'Upload', 'group' => 'Archives (à dépublier)'])
        ->call('setTemplate', ['outer_list_rows_mosaic', 'admin/media/mosaic.html.twig'])
    ;
    $services
        ->set('app.admin.city', App\Admin\CityAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\City::class, 'label' => 'Communes', 'group' => 'Archives (à dépublier)'])
    ;
    $services
        ->set('app.admin.user_list_definition', App\Admin\UserListDefinitionAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\UserListDefinition::class, 'label' => 'Tech » Labels', 'group' => 'Archives (à dépublier)'])
    ;
    $services
        ->set('app.admin.petition_signature', App\Admin\PetitionSignatureAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\PetitionSignature::class, 'label' => 'Signatures', 'group' => 'Pétitions'])
    ;
    $services
        ->set('app.admin.formation_path', App\Admin\Formation\PathAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\Formation\Path::class, 'label' => 'Parcours', 'group' => 'Archives » Formation'])
    ;
    $services
        ->set('app.admin.formation_axe', App\Admin\Formation\AxeAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\Formation\Axe::class, 'label' => 'Axes de formation', 'group' => 'Archives » Formation'])
    ;
    $services
        ->set('app.admin.formation_module', App\Admin\Formation\ModuleAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\Formation\Module::class, 'label' => 'Modules', 'group' => 'Archives » Formation'])
    ;
    $services
        ->set('app.admin.mooc_mooc', App\Admin\MoocAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\Mooc\Mooc::class, 'label' => 'MOOC', 'group' => 'Archives » Formation'])
    ;
    $services
        ->set('app.admin.mooc_chapter', App\Admin\MoocChapterAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\Mooc\Chapter::class, 'label' => 'Chapitres MOOC', 'group' => 'Archives » Formation'])
    ;
    $services
        ->set('app.admin.mooc_element', App\Admin\MoocElementAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\Mooc\BaseMoocElement::class, 'label' => 'Éléments MOOC', 'group' => 'Archives » Formation'])
        ->call('setSubClasses', [[
            'Image' => App\Entity\Mooc\MoocImageElement::class,
            'Vidéo' => App\Entity\Mooc\MoocVideoElement::class,
            'Quiz' => App\Entity\Mooc\MoocQuizElement::class,
        ]])
    ;
    $services
        ->set('app.admin.custom_search_result', App\Admin\CustomSearchResultAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\CustomSearchResult::class, 'label' => 'Contenu » Résultats Algolia', 'group' => 'Archives'])
    ;
    $services
        ->set('app.admin.facebook_video', App\Admin\FacebookVideoAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\FacebookVideo::class, 'label' => 'Contenu » Vidéos Facebook', 'group' => 'Archives'])
    ;
    $services
        ->set('app.admin.social_share_category', App\Admin\SocialShareCategoryAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\SocialShareCategory::class, 'label' => 'Je Partage » Catégories', 'group' => 'Archives'])
    ;
    $services
        ->set('app.admin.social_share', App\Admin\SocialShareAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\SocialShare::class, 'label' => 'Je Partage » Contenus', 'group' => 'Archives'])
    ;
    $services
        ->set('app.admin.facebook_profile', App\Admin\FacebookProfileAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\FacebookProfile::class, 'label' => 'Profils Facebook', 'group' => 'Archives'])
    ;
    $services
        ->set('app.admin.legislative_district_zone', App\Admin\LegislativeDistrictZoneAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\LegislativeDistrictZone::class, 'label' => 'Zones géographiques', 'group' => 'Archives'])
    ;
    $services
        ->set('app.admin.approach', App\Admin\ProgrammaticFoundation\ApproachAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\ProgrammaticFoundation\Approach::class, 'label' => 'Grand axes', 'group' => 'Archives » Socle programmatique'])
    ;
    $services
        ->set('app.admin.approach_sub', App\Admin\ProgrammaticFoundation\SubApproachAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\ProgrammaticFoundation\SubApproach::class, 'label' => 'Axes secondaires', 'group' => 'Archives » Socle programmatique'])
    ;
    $services
        ->set('app.admin.approach_measure', App\Admin\ProgrammaticFoundation\MeasureAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\ProgrammaticFoundation\Measure::class, 'label' => 'Mesures', 'group' => 'Archives » Socle programmatique'])
    ;
    $services
        ->set('app.admin.approach_project', App\Admin\ProgrammaticFoundation\ProjectAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\ProgrammaticFoundation\Project::class, 'label' => 'Projets', 'group' => 'Archives » Socle programmatique'])
    ;
    $services
        ->set('app.admin.programmatic_foundation_tag', App\Admin\ProgrammaticFoundation\TagAdmin::class)
        ->tag('sonata.admin', ['manager_type' => 'orm', 'model_class' => App\Entity\ProgrammaticFoundation\Tag::class, 'label' => 'Tags', 'group' => 'Archives » Socle programmatique'])
    ;
    $services
        ->set('app.admin.algolia_indexed_entity_extension', App\Admin\Extension\AlgoliaIndexedEntityAdminExtension::class)
        ->tag('sonata.admin.extension', ['target' => 'app.admin.custom_search_result'])
        ->tag('sonata.admin.extension', ['target' => 'app.admin.proposal'])
    ;
    $services
        ->set('app.admin.softdeleteable_filter_configuration_extension', App\Admin\Extension\DoctrineSoftdeleteableFilterConfigurationAdminExtension::class)
        ->tag('sonata.admin.extension', ['target' => 'app.admin.report'])
    ;
    $services
        ->set('app.admin.filter_by_zone_extension', App\Admin\Extension\FilterByZonesAdminExtension::class)
        ->tag('sonata.admin.extension', ['target' => 'app.admin.adherent'])
        ->tag('sonata.admin.extension', ['target' => 'app.admin.adherent_elected_representative'])
        ->tag('sonata.admin.extension', ['target' => 'app.admin.national_event_inscriptions'])
    ;
    $services
        ->set('app.admin.entity_administrator_blameable_extension', App\Admin\Extension\EntityAdministratorBlameableAdminExtension::class)
        ->tag('sonata.admin.extension', ['priority' => '-256'])
        ->tag('sonata.admin.extension', ['target' => 'app.admin.adherent'])
        ->tag('sonata.admin.extension', ['target' => 'app.admin.cms_block'])
        ->tag('sonata.admin.extension', ['target' => 'app.admin.team.team'])
        ->tag('sonata.admin.extension', ['target' => 'app.admin.phoning.campaign'])
        ->tag('sonata.admin.extension', ['target' => 'app.admin.jecoute_national_survey'])
        ->tag('sonata.admin.extension', ['target' => 'app.admin.jecoute_local_survey'])
        ->tag('sonata.admin.extension', ['target' => 'app.admin.voting_platform.designation'])
        ->tag('sonata.admin.extension', ['target' => 'app.admin.local_election.candidacies_group'])
        ->tag('sonata.admin.extension', ['target' => 'app.admin.adherent_formation'])
        ->tag('sonata.admin.extension', ['target' => 'app.admin.general_meeting_report'])
        ->tag('sonata.admin.extension', ['target' => 'app.admin.email_template'])
        ->tag('sonata.admin.extension', ['target' => 'app.admin.consultation'])
        ->tag('sonata.admin.extension', ['target' => 'app.admin.chatbot_chatbot'])
        ->tag('sonata.admin.extension', ['target' => 'app.admin.procuration_v2.election'])
        ->tag('sonata.admin.extension', ['target' => 'app.admin.hub_item'])
        ->tag('sonata.admin.extension', ['target' => 'app.admin.transactional_email_template'])
        ->tag('sonata.admin.extension', ['target' => 'app.admin.jecoute.news'])
        ->tag('sonata.admin.extension', ['target' => 'app.admin.live_stream'])
        ->tag('sonata.admin.extension', ['target' => 'app.admin.agora'])
        ->tag('sonata.admin.extension', ['target' => 'app.admin.national_event'])
        ->tag('sonata.admin.extension', ['target' => 'app.admin.alert'])
        ->tag('sonata.admin.extension', ['target' => 'app.admin.private_message'])
    ;

    $services
        ->set('app.admin.entity_scope_visibility_extension', App\Admin\Extension\EntityScopeVisibilityAdminExtension::class)
        ->tag('sonata.admin.extension', ['target' => 'app.admin.team.team'])
        ->tag('sonata.admin.extension', ['target' => 'app.admin.phoning.campaign'])
        ->tag('sonata.admin.extension', ['target' => 'app.admin.adherent_formation'])
        ->tag('sonata.admin.extension', ['target' => 'app.admin.general_meeting_report'])
    ;
    $services
        ->set('app.admin.manage_media_extension', App\Admin\Extension\ManageMediaExtension::class)
        ->tag('sonata.admin.extension', ['target' => 'app.admin.formation_module'])
        ->tag('sonata.admin.extension', ['target' => 'app.admin.formation_axe'])
    ;
    $services
        ->set('app.admin.image_relation_upload_extension', App\Admin\Extension\ImageRelationUploadExtension::class)
        ->tag('sonata.admin.extension', ['target' => 'app.admin.mooc_mooc'])
        ->tag('sonata.admin.extension', ['target' => 'app.admin.mooc_element'])
    ;
    $services
        ->set('app.admin.simple_image_upload_extension', App\Admin\Extension\SimpleImageUploadExtension::class)
        ->tag('sonata.admin.extension', ['target' => 'app.admin.jecoute.resource.link'])
        ->tag('sonata.admin.extension', ['target' => 'app.admin.je_mengage.header_block'])
    ;
};
