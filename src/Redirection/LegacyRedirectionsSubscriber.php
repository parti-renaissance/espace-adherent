<?php

namespace App\Redirection;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LegacyRedirectionsSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'kernel.exception' => 'onKernelException',
        ];
    }

    public function onKernelException(GetResponseForExceptionEvent $event): void
    {
        if (!$event->getException() instanceof NotFoundHttpException) {
            return;
        }

        $requestUri = rtrim($event->getRequest()->getPathInfo(), '/');

        if (isset(self::$redirections[$requestUri])) {
            $redirectUri = self::$redirections[$requestUri];

            if ($queryString = $event->getRequest()->getQueryString()) {
                $redirectUri .= '?'.$queryString;
            }

            $event->setResponse(new RedirectResponse($redirectUri, 301));
        }
    }

    private static $redirections = [
        '/discours-a-luniversite-des-gracques' => '/article/discours-a-luniversite-des-gracques',
        '/la-data-au-service-de-linteret-general' => '/article/la-data-au-service-de-linteret-general',
        '/voeux-aux-acteurs-economiques' => '/article/voeux-aux-acteurs-economiques',
        '/pour-leurope-il-est-temps-de-passer-a-la-prochaine-etape' => '/article/pour-leurope-il-est-temps-de-passer-a-la-prochaine-etape',
        '/ladie-le-microcredit-pour-creer-son-entreprise' => '/article/ladie-le-microcredit-pour-creer-son-entreprise',
        '/retrouver-lesprit-industriel-du-capitalisme' => '/article/retrouver-lesprit-industriel-du-capitalisme',
        '/pour-plus-degalite-dans-lacces-aux-etudes-superieures' => '/article/pour-plus-degalite-dans-lacces-aux-etudes-superieures',
        '/year-up-aide-les-jeunes-a-developper-leur-potentiel' => '/article/year-up-aide-les-jeunes-a-developper-leur-potentiel',
        '/discriminations-religieuses-a-lembauche-une-realite' => '/article/discriminations-religieuses-a-lembauche-une-realite',
        '/passeport-avenir-pour-une-egalite-reelle' => '/article/passeport-avenir-pour-une-egalite-reelle',
        '/bertin-nahum-entrepreneur-revolutionnaire' => '/article/bertin-nahum-entrepreneur-revolutionnaire',
        '/lecole-42-un-nouveau-type-decole' => '/article/lecole-42-un-nouveau-type-decole',
        '/transformer-les-technologies-en-solutions' => '/article/transformer-les-technologies-en-solutions',
        '/reinvestir-les-trois-reves-francais' => '/article/reinvestir-les-trois-reves-francais',
        '/linnovation-sociale-groupe-sos' => '/article/linnovation-sociale-groupe-sos',
        '/saisir-les-nouvelles-opportunites-economiques' => '/article/saisir-les-nouvelles-opportunites-economiques',
        '/echanges-sur-le-numerique' => '/article/echanges-sur-le-numerique',
        '/lentrepreneuriat-pour-developper-les-quartiers-prioritaires' => '/article/lentrepreneuriat-pour-developper-les-quartiers-prioritaires',
        '/limparfait-du-politique' => '/article/limparfait-du-politique',
        '/la-grande-marche' => '/article/la-grande-marche',
        '/propositions-refonder-leurope' => '/article/propositions-refonder-leurope',
        '/la-ruche-qui-dit-oui' => '/article/la-ruche-qui-dit-oui',
        '/lidentite-francaise-selon-e-macron' => '/article/lidentite-francaise-selon-e-macron',
        '/grande-marche-demarrera-samedi-28-mai' => '/article/grande-marche-demarrera-samedi-28-mai',
        '/sommes-plus-de-50-000' => '/article/sommes-plus-de-50-000',
        '/cest-parti' => '/article/cest-parti',
        '/17-temoignages-volontaires-porte-a-porte-en-marche' => '/article/17-temoignages-volontaires-porte-a-porte-en-marche',
        '/retablir-la-verite' => '/article/retablir-la-verite',
        '/emmanuel-macron-invite-rtl-vendredi-17-juin' => '/article/emmanuel-macron-invite-rtl-vendredi-17-juin',
        '/interview-demmanuel-macron-monde-devons-delivrer-leurope-de-devenue' => '/article/interview-demmanuel-macron-monde-devons-delivrer-leurope-de-devenue',
        '/europe-et-maintenant-que-fait-on' => '/article/europe-et-maintenant-que-fait-on',
        '/retrouvez-lentretien-demmanuel-macron-voix-nord' => '/article/retrouvez-lentretien-demmanuel-macron-voix-nord',
        '/grande-marche-grande-boucle' => '/article/grande-marche-grande-boucle',
        '/live-tousenmarche' => '/article/live-tousenmarche',
        '/rassemblement-tous-en-marche' => '/article/live-tousenmarche',
        '/emmanuel-macron-laicite' => '/article/emmanuel-macron-laicite',
        '/axelle-tessandier-marche' => '/article/axelle-tessandier-marche',
        '/patrick-toulmet-marche' => '/article/patrick-toulmet-marche',
        '/emmanuel-macron-lidentite-francaise' => '/article/emmanuel-macron-lidentite-francaise',
        '/lengagement-de-jeunesse-zineb' => '/article/lengagement-de-jeunesse-zineb',
        '/projet-europeen-demmanuel-macron' => '/article/projet-europeen-demmanuel-macron',
        '/en-marche-rassemble' => '/article/en-marche-rassemble',
        '/lhommage-demmanuel-macron-a-michel-rocard-2' => '/article/lhommage-demmanuel-macron-a-michel-rocard-2',
        '/deplacement-demmanuel-macron-vendee' => '/article/deplacement-demmanuel-macron-vendee',
        '/ca-reveille-gaspillage-alimentaire' => '/article/ca-reveille-gaspillage-alimentaire',
        '/suite-a-demission-allocution-demmanuel-macron' => '/article/suite-a-demission-allocution-demmanuel-macron',
        '/retrouvez-lallocution-directe-demmanuel-macron' => '/article/retrouvez-lallocution-directe-demmanuel-macron',
        '/interview-demmanuel-macron-tf1' => '/article/interview-demmanuel-macron-tf1',
        '/emmanuel-macron-a-foire-agricole-de-chalons-champagne' => '/article/emmanuel-macron-a-foire-agricole-de-chalons-champagne',
        '/lecole-42-born-to-change' => '/article/lecole-42-born-to-change',
        '/emmanuel-macron-france-inter-franceinfotv' => '/article/emmanuel-macron-france-inter-franceinfotv',
        '/emmanuel-macron-jdd' => '/article/emmanuel-macron-jdd',
        '/emmanuel-macron-bondy-blog' => '/article/emmanuel-macron-bondy-blog',
        '/fracture-numerique-nest-legende-personne-deux-diplome-na-acces-a-internet' => '/article/fracture-numerique-nest-legende-personne-deux-diplome-na-acces-a-internet',
        '/25-millions-de-personnes-ayant-ete-scolarisees-france-situation-dillettrisme-soit-autant-population-de-marseille-lyon-lille-strasbourg-bordeaux-toulouse-reunies' => '/article/25-millions-de-personnes-ayant-ete-scolarisees-france-situation-dillettrisme-soit-autant-population',
        '/journees-europeennes-patrimoine-camarche' => '/article/journees-europeennes-patrimoine-camarche',
        '/interview-demmanuel-macron-ruth-elkrief' => '/article/interview-demmanuel-macron-ruth-elkrief',
        '/emmanuel-macron-sommet-reformistes-europeens' => '/article/emmanuel-macron-sommet-reformistes-europeens',
        '/rendez-debut-octobre' => '/article/le-diagnostic',
        '/live-lafrancequisubit-strasbourg-macron' => '/article/live-lafrancequisubit-strasbourg-macron',
        '/diagnostic-de-lafrancequisubit-2eme-etape' => '/article/diagnostic-de-lafrancequisubit-2eme-etape',
        '/emmanuel-macron-repond-rtl' => '/article/emmanuel-macron-repond-rtl',
        '/le-diagnostic' => '/article/le-diagnostic',
        '/diagnostic-de-lafrancequiunit-3eme-etape' => '/article/diagnostic-de-lafrancequiunit-3eme-etape',
        '/emmanuel-macron-direct-de-mediapart-novembre-2016' => '/article/emmanuel-macron-direct-de-mediapart-novembre-2016',
        '/rien-nest-jamais-ecrit-a-lavance' => '/article/rien-nest-jamais-ecrit-a-lavance',
        '/discours-demmanuel-macron-16-novembre-2016' => '/article/discours-demmanuel-macron-16-novembre-2016',
        '/decouvrez-revolution' => '/article/decouvrez-revolution',
        '/macron-appelle-a-progressistes' => '/article/macron-appelle-a-progressistes',
        '/interview-demmanuel-macron-jean-jacques-bourdin' => '/article/interview-demmanuel-macron-jean-jacques-bourdin',
        '/interview-demmanuel-macron-public-senat' => '/article/interview-demmanuel-macron-public-senat',
        '/rendez-10-decembre-a-paris' => '/article/rendez-10-decembre-a-paris',
        '/emmanuel-macron-vie-politique' => '/article/emmanuel-macron-vie-politique',
        '/emmanuel-macron-a-bordeaux' => '/article/emmanuel-macron-a-bordeaux',
        '/tribune-de-richard-ferrand' => '/article/tribune-de-richard-ferrand',
        '/signez-lappel-marchent' => '/article/signez-lappel-marchent',
        '/outre-mer-lun-piliers-de-richesse-culturelle' => '/article/outre-mer-lun-piliers-de-richesse-culturelle',
        '/participez-a-rassemblement-enmarche' => '/article/participez-a-rassemblement-enmarche',
        '/voeux-de-richard-ferrand' => '/article/voeux-de-richard-ferrand',
        '/tribune-demmanuel-macron-journal-monde' => '/article/tribune-demmanuel-macron-journal-monde',
        '/nevers-clermont-ferrand-prendre-pouvoir-rendre' => '/article/nevers-clermont-ferrand-prendre-pouvoir-rendre',
        '/penser-printemps' => '/article/penser-printemps',
        '/croire-a-reves-lille-14-janvier-2017' => '/article/croire-a-reves-lille-14-janvier-2017',
        '/direct-de-quimper' => '/article/direct-de-quimper',
        '/feter-france-marche' => '/article/feter-france-marche',
        '/construire-majorite-de-projet' => '/article/construire-majorite-de-projet',
        '/telemedecine-soins-lon-soit' => '/article/telemedecine-soins-lon-soit',
        '/agriculture-prendre-virage-qualitatif' => '/article/agriculture-prendre-virage-qualitatif',
        '/mensonges-en-marche-repond' => '/article/mensonges-en-marche-repond',
        '/un-projet-pour-la-culture' => '/article/un-projet-pour-la-culture',
        '/france-moyen-orient-deux-destins-lies-construire-paix' => '/article/france-moyen-orient-deux-destins-lies-construire-paix',
        '/je-donne' => '/don',
        '/mentions-legales' => '/mentions-legales',
        '/organisation.html' => '/le-mouvement/notre-organisation',
        '/ca-nous-fait-marcher' => '/articles/actualites',
        '/emmanuel-macron' => '/emmanuel-macron',
        '/en-marche' => '/le-mouvement',
        '/suivez-en-marche' => '/le-mouvement',
    ];
}
