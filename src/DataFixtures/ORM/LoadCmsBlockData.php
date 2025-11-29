<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\AdherentCharter\AdherentCharterTypeEnum;
use App\Entity\CmsBlock;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadCmsBlockData extends Fixture
{
    private const MARKDOWN_CONTENT = <<<MARKDOWN
        # Lorem ipsum

        Lorem ipsum dolor sit amet, consectetur adipiscing elit.
        MARKDOWN;

    public function load(ObjectManager $manager): void
    {
        $manager->persist($this->createCmsBlock(
            'phoning-campaign-tutorial',
            'Tutorial pour les campagnes de Phoning'
        ));

        $manager->persist($this->createCmsBlock(
            'pap-campaign-tutorial',
            'Tutorial pour les campagnes de PAP',
            '**Texte du tutoriel** pour la *campagne* de PAP avec le Markdown'
        ));

        $manager->persist($this->createCmsBlock(
            \sprintf('chart-%s', AdherentCharterTypeEnum::TYPE_PHONING_CAMPAIGN),
            'Charte pour les appelants'
        ));

        $manager->persist($this->createCmsBlock(
            \sprintf('chart-%s', AdherentCharterTypeEnum::TYPE_PAP_CAMPAIGN),
            'Charte pour les militant de la campagnes de PAP',
            '**Texte de la charte** pour la *campagne* de PAP avec le Markdown'
        ));

        $manager->persist($this->createCmsBlock(
            'rgpd',
            'Affiché en bas du formulaire d\'inscription',
            'Les données recueillies sur ce formulaire sont traitées par LaREM afin de gérer les informations relatives aux inscriptions aux évènements de LaREM et de permettre à LaREM de vous envoyer des communications politiques.
Si vous êtes élu(e) ou ancien(ne) élu(e), nous traitons également vos données dans le cadre de l’animation de notre réseau d’élu(e)s et vos données peuvent être transférer à La République Ensemble ou à l’institut de formation Tous Politiques, conformément à la politique de protection des données des élu(e)s. Toutes les informations sont obligatoires, sauf celles marquées "Optionnel". L’absence de réponse dans ces champs ne permettra pas à LaREM de traiter votre demande.
Conformément à la règlementation, vous disposez d’un droit d’opposition et d’un droit à la limitation du traitement de données vous concernant, ainsi que d’un droit d’accès, de rectification, de portabilité et d’effacement de vos données.
Vous disposez également de la faculté de donner des directives sur le sort de vos données après votre décès.
Vous pouvez consulter notre Politique de protection des données (si vous êtes élu(e)s, la Politique de protection des données des élu(e)s) et exercer vos droits en nous adressant votre demande accompagnée d’une copie de votre pièce d’identité à l’adresse postale : La République En Marche, 68 rue du Rocher, 75008 Paris, France ou électronique suivante : **mes-donnees@en-marche.fr **ou encore en contactant notre DPO à l’adresse : **dpo@en-marche.fr**.'
        ));

        $manager->persist($this->createCmsBlock(
            'je-mengage-mentions-legales',
            'Mentions légales du site je-mengage.fr',
            file_get_contents(__DIR__.'/../legalities.md')
        ));

        $manager->persist($this->createCmsBlock(
            'je-mengage-politique-protection-donnees',
            'Politique de protection des données à caractère personnel du je-mengage.fr',
            file_get_contents(__DIR__.'/../data_policy.md')
        ));

        $manager->persist($this->createCmsBlock(
            'procuration-faq',
            'Texte d\'aide pour les procuration (FAQ)',
            <<<'TXT'
                Vous avez des questions concernant les modalités du vote par procuration ? <a href="https://aide.en-marche.fr/category/729-vote-par-procuration">Cliquez ici !</a>
                <br /><br />
                Si vous avez besoin d'aide pour remplir le formulaire en ligne, n'hésitez pas à nous contacter au 01&nbsp;86&nbsp;95&nbsp;84&nbsp;20 en précisant votre nom, votre prénom, la ville ainsi que votre numéro de téléphone que nous puissions vous rappeler dans les plus brefs délais.
                À très bientôt.
                TXT
        ));

        $manager->persist($this->createCmsBlock(
            'procuration-support-contact',
            'Texte affichant les informations de contact au support',
            <<<'TXT'
                En cas de question, n'hésitez pas à nous contacter au <a href="tel:+33186950286">01 86 95 02 86</a>
                ou à <a href="mailto:legislatives@avecvous.fr">legislatives@avecvous.fr</a>.
                TXT
        ));

        $manager->persist($this->createCmsBlock(
            'procuration-legal-notices',
            'Mentions obligatoires sous les formulaires de demande et de proposition de procuration',
            <<<'TXT'
                Les informations marquées d'un astérisque sont obligatoires, l'absence de réponse de ces champs
                ne permettra pas à LaREM de traiter votre demande. Les données recueillies dans ce formulaire sont
                traitées par LaREM dans le cadre de la plateforme de procuration, afin (i) d'identifier des
                personnes susceptibles d'être mandataires en vue d'obtenir la procuration d'un tiers dans le cadre
                des élections municipales 2020, (ii) d'identifier des personnes susceptibles d'être mandants en vue
                d'octroyer une procuration à un tiers dans le cadre des élections municipales 2020, et (iii) de
                mettre en relation des mandataires et des mandants afin que les premiers puissent recevoir
                la procuration des seconds dans le cadre des élections municipales 2020.<br/><br/>

                Si vous y consentez, ces informations peuvent également être utilisées par LaREM afin de vous
                contacter pour la prochaine échéance électorale, à savoir les élections départementales 2021.<br/><br/>

                Ces informations sont nécessaires pour vous permettre de devenir mandant et peuvent être
                communiquées aux services administratifs habilités par LaREM et chargés de la gestion des
                procurations, comme nos responsables procuration, les mairies et bureaux de vote.<br/><br/>

                Conformément à la règlementation en vigueur, vous disposez d'un droit d'opposition et d'un droit à la limitation du traitement de données vous concernant, ainsi que d'un droit d'accès, de rectification, de portabilité et d'effacement de vos données. Vous disposez également de la faculté de donner des directives sur le sort de vos données après votre décès. Vous pouvez exercer vos droits en nous adressant votre demande accompagnée d'une copie de votre pièce d'identité à l'adresse postale ou électronique suivante : La République En Marche, 68 rue du Rocher, 75008 Paris, France ou <a target="_blank" class="link--no-decor" href="mailto:mes-donnees@en-marche.fr">mes-donnees@en-marche.fr</a>. Pour plus d'information vous pouvez consulter la Politique de protection des données de LaREM <a href="https://en-marche.fr/politique-protection-donnees" target="_blank" rel="noopener noreferrer" class="link--no-decor">https://en-marche.fr/politique-protection-donnees</a>.
                TXT
        ));

        $manager->persist($this->createCmsBlock(
            'procuration-reason-residency-warning',
            'Texte affiché pour le rappel de validation de procuration en physique',
            <<<'TXT'
                Nous vous rappelons que pour faire valider votre procuration, il vous sera nécéssaire
                de vous déplacer dans un commissariat, gendarmerie ou tribunal d'instance (si vous habitez
                en France) ou dans une ambassade ou consulat de France (si vous résidez à l'étranger).
                TXT
        ));

        $manager->persist($this->createCmsBlock(
            'procuration-proposal-cta',
            'Texte affiché avant le call-to-action "Je me porte mandataire"',
            'Je me propose pour voter au nom d’un(e) citoyen(e) près de chez moi'
        ));

        $manager->persist($this->createCmsBlock(
            'procuration-request-cta',
            'Texte affiché avant le call-to-action "Je donne procuration"',
            'Je souhaite qu’un(e) citoyen(e) vote en mon nom'
        ));

        $manager->persist($this->createCmsBlock(
            'procuration-proposal-form-header',
            'Texte affiché au début du formulaire de proposition de procuration',
            'Portez la voix d’un citoyen près de chez vous'
        ));

        $manager->persist($this->createCmsBlock(
            'procuration-proxy-thanks-content',
            'Texte affiché pour la page de confirmation de proposition de procuration',
            <<<'TXT'
                <h1 class="text--large">
                    Merci! Votre proposition est bien prise en compte.
                </h1>
                <p class="text--body">
                    Quelles sont les prochaines étapes?
                    <ol>
                        <li>Si nous recevons une demande dans votre commune ou dans l’une de celles où vous nous avez notifié pouvoir vous rendre, nous réaliserons un «match» entre vous.</li>
                        <li>Vous recevrez un email identique vous indiquant vos coordonnées respectives.</li>
                        <li>Prenez contact l’un avec l’autre et confirmez la mission entre vous.</li>
                        <li>Attendez que le mandant réalise les démarches. N’hésitez pas à prendre des nouvelles régulièrement si vous n’en avez pas.</li>
                        <li>Vous pourrez voter le jour J en présentant simplement votre propre pièce d’identité (vous n’avez besoin d’aucun document de la part de mandant). Assurez-vous de bien connaitre l’adresse du bureau de vote à l’avance.</li>
                    </ol>
                </p>
                TXT
        ));

        $manager->persist($this->createCmsBlock(
            'procuration-request-thanks-content',
            'Texte affiché pour la page de confirmation de demande de procuration',
            <<<'TXT'
                <h1 class="text--large">
                    Merci! Votre demande est bien prise en compte.
                </h1>
                <p class="text--body">
                    Quelles sont les prochaines étapes?
                    <ol>
                        <li>Nous vous cherchons un mandataire</li>
                        <li>Quand nous l’avons trouvé, nous vous envoyons à tous les deux le même email avec vos coordonnées respectives.</li>
                        <li>Prenez contact l’un avec l’autre et confirmez la mission entre vous.</li>
                        <li>
                            Vous, le mandant, devez-vous rendre dans un commissariat ou une gendarmerie (si vous habitez en France) ou dans un consulat de France (si vous résidez à l'étranger) pour établir la procuration. Vous pouvez aussi gagner du temps dans la démarche et faire l’opération en ligne sur https://www.maprocuration.gouv.fr :

                            Notez que vous devrez quand même vous rendre dans les lieux précédemment cités pour vérifier votre identité et valider la procédure.

                            Notez aussi que vous devrez être en possession de votre numéro d’électeur pour faire la procédure sur le site https://www.maprocuration.gouv.fr ou au commissariat / gendarmerie.
                        </li>
                        <li>N’oubliez pas d’informer votre mandataire quand la procuration est bien acceptée. Donnez lui toutes les indications nécessaires pour son déplacement (adresse du bureau de vote, etc.). Vous n’avez aucun document particulier à lui donner, il votera en votre nom en présentant sa propre pièce d’identité.</li>
                    </ol>
                </p>
                TXT
        ));

        $manager->persist($this->createCmsBlock(
            'procuration-request-form-intro',
            'Texte affiché avant le formulaire de demande de procuration',
            <<<'TXT'
                Nous pouvons vous trouver quelqu’un qui portera votre voix le jour de l’élection.
                Remplissez simplement le formulaire.
                Attention, la procuration doit ensuite parvenir à votre bureau de vote, ce qui prend plusieurs jours.
                Effectuez donc votre démarche rapidement !
                TXT
        ));

        $manager->persist($this->createCmsBlock(
            'assesseur-request-subtitle',
            'Texte affiché sous le titre de la page de demande des assesseurs',
            'Élections municipales 2020.'
        ));

        $manager->persist($this->createCmsBlock(
            'assesseur-request-homepage',
            'Texte affiché sur la page du formulaire de demande',
            <<<'TXT'
                <p class="text--gray">
                    Madame, Monsieur,<br/><br/>
                    Merci de vous porter volontaire pour être assesseur(e) lors des élections municipales de 2020.<br/><br/>
                    Ce formulaire vous permettra de renseigner l'ensemble des informations nécessaires.<br/><br/>
                    En raison du nombre limité de places par bureau de vote pour chaque liste (1 assesseur(e) titulaire et 1 assesseur(e) suppléant(e)),
                    nous vous invitons à en sélectionner plusieurs dans l’ordre de vos préférences. Nous en tiendrons compte au maximum lors des attributions.<br/>
                    Vous recevrez prochainement un email de confirmation et d’explication si votre candidature est retenue.<br/>
                    <br/>
                    Merci encore pour votre engagement !<br/><br/>
                    Conditions préalables pour devenir assesseur :<br/>
                    <ol>
                        <li>Vous pouvez être assesseur dans n’importe quelle commune du département où vous habitez.</li>
                        <li>Vous devez être âgée de 18 ans minimum.</li>
                    </ol>
                </p>
                TXT
        ));

        $manager->persist($this->createCmsBlock(
            'assesseur-request-mentions-legales',
            'Mentions légales du formulaires de demande assesseur',
            <<<'TXT'
                <p class="b__nudge--top-large text--small text--gray text--justify">
                   Les données recueillies dans ce formulaire sont traitées par La République en Marche (LaREM) et ses équipes aux fins
                   de gestion des demandes à tenir le rôle d’assesseur lors de l’élection présidentielle qui se déroulera les 10 et 24 avril 2022.
                   Elles permettront à LaREM de vous contacter dans ce cadre et si vous y consentez, de vous recontacter pour tenir le rôle d’assesseur lors de la prochaine échéance électorale.
                   En envoyant ce formulaire, vous autorisez LaREM à utiliser vos données pour ces finalités. Vos données seront conservées jusqu’à la fin de l’élection présidentielle 2022
                   ou à la fin des élections législatives 2022 lorsque vous avez consenti à être recontacté pour la prochaine échéance électorale.<br/><br/>

                   Conformément à la règlementation en vigueur sur la protection des données, vous pouvez retirer votre consentement à tout moment. Vous disposez d’un droit d’opposition
                   et d’un droit de limitation du traitement de vos données, ainsi que d’un droit d’accès, de rectification, de portabilité et d’effacement de vos données.
                   Vous disposez également de la faculté de donner des directives sur le sort de vos données. Vous pouvez exercer vos droits en nous adressant votre demande à l’adresse postale : La République en marche, 68 rue du rocher 75008 Paris
                   ou à l’adresse électronique <a href="mailto:mes-donnees@en-marche.fr" target="_blank" class="link--no-decor">mes-donnees@en-marche.fr</a>. Pour toute information relative au traitement de vos données,
                   consultez notre politique de protection des données [insérer le lien vers la politique de protection des données du site qui porte le formulaire] ou contactez notre délégué à la protection des données à l’adresse <a href="mailto:dpo@en-marche.fr" target="_blank" class="link--no-decor">dpo@en-marche.fr</a>.
                </p>
                TXT
        ));

        $manager->persist($this->createCmsBlock(
            'donation-index-header-content',
            'Texte affiché dans le header de la page d\'accueil des dons',
            <<<'TXT'
                <h1 class="text--larger">
                    Donnons une majorité au Président de la République les 12 et 19 juin
                </h1>
                TXT
        ));

        $manager->persist($this->createCmsBlock(
            'donation-index-why-content',
            'Texte affiché dans la section "Pourquoi donner?" de la page d\'accueil des dons',
            <<<'TXT'
                <h2 class="text--large b__nudge--bottom-larger">Pourquoi faire un don ?</h2>
                <p class="text--body">
                    Grâce aux ressources générées par notre représentation dans la vie politique nationale,
                    votre générosité fidèle depuis 2017 et grâce à la bonne gestion de notre Mouvement,
                    <b>La République en Marche a financé une grande partie de la campagne présidentielle
                    et apporte son soutien aux candidats de la Majorité Présidentielle
                    pour les élections législatives des 12 et 19 juin.</b>
                </p>
                <p class="text--body">
                    Mais nous avons besoin de compléter cet effort afin de continuer à mener des actions
                    à la hauteur de nos ambitions, pour continuer d’atteindre l’ensemble de nos concitoyens,
                    notamment ceux qui sont les plus éloignés de la vie politique,
                    les écouter et échanger avec eux. Les prochaines échéances sont d’une importance fondamentale
                    pour la mise en place des réformes annoncées par le Président de la République.
                </p>
                <p class="text--body">
                    Chaque don, peu importe le montant, sera une aide précieuse.
                    C’est à chaque fois un acte d’engagement, c’est se mobiliser pour la réussite de notre pays auprès
                    de notre Président. C’est continuer de faire de la politique autrement en nous donnant les moyens
                    de gagner au mois de juin.
                </p>
                <p class="text--body">
                    En 2017, vous étiez déjà 100 000 donateurs à soutenir nos actions.
                    En 2022, nous avons encore besoin de vous tous!
                </p>
                TXT
        ));

        $manager->persist($this->createCmsBlock(
            'donation-index-transparence-content',
            'Texte affiché dans la partie "Transparence" de la page d\'accueil des dons',
            <<<'TXT'
                <h2 class="text--large b__nudge--bottom-larger">La transparence</h2>
                <div class="">
                    <p class="text--body">
                        Soutenez-nous en toute confiance : dans toutes nos démarches, nos équipes veillent au quotidien à respecter
                        toutes les exigences instaurées par le code électoral et par la loi sur la transparence financière.
                        Nos comptes sont certifiés par deux commissaires aux comptes et transmis à la Commission Nationale des
                        Comptes de Campagne et des Financements Politiques (CNCCFP) qui contrôle le respect des obligations
                        légales des partis politiques et assure la publication des comptes au Journal Officiel.
                    </p>
                </div>
                TXT
        ));

        $manager->persist($this->createCmsBlock(
            'donation-index-reduction-content',
            'Texte affiché dans la partie "Réduction" de la page d\'accueil des dons',
            <<<'TXT'
                <h2 class="text--large b__nudge--bottom-larger">La réduction fiscale</h2>
                <p class="text--body">
                    66 % de votre don vient en réduction de votre impôt sur le revenu (dans la limite de 20 % du revenu imposable).
                </p>
                <div class="donation-reduction__cols">
                    <p class="text--body donation-reduction__col">
                        <span class="donation-highlight">Par exemple :</span> un don de 100 € vous revient en réalité à 34 € et vous fait bénéficier d’une réduction d’impôt
                        de 66 €. Le montant annuel de votre don ne peut pas excéder 7500 € par personne physique tous partis politiques confondus.
                        <br /><br />
                        <strong>Le reçu fiscal pour votre don de l’année N vous sera envoyé au 2e trimestre de l’année N+1.</strong>
                        <br /><br />
                        <span class="donation-highlight">Par exemple :</span> pour un don effectué en 2022, le reçu fiscal vous sera envoyé vers le mois de mai ou juin en 2023.

                    </p>
                    <p class="text--body donation-reduction__col">
                        Le reçu fiscal est édité par la Commission Nationale des Comptes de Campagne et des Financements Politiques (CNCCFP) après la vérification de la liste des donateurs transmise par le mouvement au plus tard le 15 avril de l’année N+1.
                        <br /><br />
                        Il est à noter que vous n’avez pas besoin du reçu fiscal pour déclarer votre don aux impôts, il est seulement nécessaire lors d’un contrôle fiscal.
                    </p>
                </div>
                TXT
        ));

        $manager->persist($this->createCmsBlock('vote-statuses-landing-page-title', '', 'Vote des statuts'));

        $manager->persist($this->createCmsBlock('vote-statuses-landing-page-content', '', <<<TXT
            <p>Lorem ipsum dolor sit amet. Eum  facilis et nemo dicta et  sint et numquam sapiente eum commodi consequatur ut dolorem natus? Sed similique itaque in  rerum ea alias voluptates ut optio odio nam officiis deleniti aut sapiente voluptates et culpa tenetur. Vel dignissimos provident et eligendi officia in animi distinctio est omnis esse vel cumque doloribus eos cumque nobis. </p><p>Qui saepe nobis et nulla natus aut enim unde est voluptatem expedita aut unde vero quo necessitatibus rerum. Vel cumque autem non corrupti ipsa sit saepe dignissimos ut exercitationem molestiae non dolores alias hic alias molestiae ut optio ipsum. </p><p>In quae accusantium et facere galisum aut repellat voluptatem sit iure autem qui iusto aliquid hic ratione ratione. Ut aspernatur nulla aut dolorum nobis ea vitae quam ad ipsa commodi. A quia voluptas ut itaque officiis sed asperiores distinctio qui quis tenetur aut quia velit et fugiat dicta nam asperiores dolore?</p>
            TXT
        ));

        $manager->persist($this->createCmsBlock('vote-statuses-landing-page-regulation', '', <<<TXT
            <p>Lorem ipsum dolor sit amet. Eum  facilis et nemo dicta et  sint et numquam sapiente eum commodi consequatur ut dolorem natus? Sed similique itaque in  rerum ea alias voluptates ut optio odio nam officiis deleniti aut sapiente voluptates et culpa tenetur. Vel dignissimos provident et eligendi officia in animi distinctio est omnis esse vel cumque doloribus eos cumque nobis. </p><p>Qui saepe nobis et nulla natus aut enim unde est voluptatem expedita aut unde vero quo necessitatibus rerum. Vel cumque autem non corrupti ipsa sit saepe dignissimos ut exercitationem molestiae non dolores alias hic alias molestiae ut optio ipsum. </p><p>In quae accusantium et facere galisum aut repellat voluptatem sit iure autem qui iusto aliquid hic ratione ratione. Ut aspernatur nulla aut dolorum nobis ea vitae quam ad ipsa commodi. A quia voluptas ut itaque officiis sed asperiores distinctio qui quis tenetur aut quia velit et fugiat dicta nam asperiores dolore?</p>
            TXT
        ));

        $manager->persist($this->createCmsBlock('vote-statuses-landing-page-designation-description', '', <<<TXT
            <p>Lorem ipsum dolor sit amet. Eum  facilis et nemo dicta et  sint et numquam sapiente eum commodi consequatur ut dolorem natus? Sed similique itaque in  rerum ea alias voluptates ut optio odio nam officiis deleniti aut sapiente voluptates et culpa tenetur. Vel dignissimos provident et eligendi officia in animi distinctio est omnis esse vel cumque doloribus eos cumque nobis. </p><p>Qui saepe nobis et nulla natus aut enim unde est voluptatem expedita aut unde vero quo necessitatibus rerum. Vel cumque autem non corrupti ipsa sit saepe dignissimos ut exercitationem molestiae non dolores alias hic alias molestiae ut optio ipsum. </p><p>In quae accusantium et facere galisum aut repellat voluptatem sit iure autem qui iusto aliquid hic ratione ratione. Ut aspernatur nulla aut dolorum nobis ea vitae quam ad ipsa commodi. A quia voluptas ut itaque officiis sed asperiores distinctio qui quis tenetur aut quia velit et fugiat dicta nam asperiores dolore?</p>
            TXT
        ));

        $manager->persist($this->createCmsBlock('voting-platform-index-election-description-local_election', '', <<<TXT
            <p>Lorem ipsum dolor sit amet. Eum  facilis et nemo dicta et  sint et numquam sapiente eum commodi consequatur ut dolorem natus? Sed similique itaque in  rerum ea alias voluptates ut optio odio nam officiis deleniti aut sapiente voluptates et culpa tenetur. Vel dignissimos provident et eligendi officia in animi distinctio est omnis esse vel cumque doloribus eos cumque nobis. </p><p>Qui saepe nobis et nulla natus aut enim unde est voluptatem expedita aut unde vero quo necessitatibus rerum. Vel cumque autem non corrupti ipsa sit saepe dignissimos ut exercitationem molestiae non dolores alias hic alias molestiae ut optio ipsum. </p><p>In quae accusantium et facere galisum aut repellat voluptatem sit iure autem qui iusto aliquid hic ratione ratione. Ut aspernatur nulla aut dolorum nobis ea vitae quam ad ipsa commodi. A quia voluptas ut itaque officiis sed asperiores distinctio qui quis tenetur aut quia velit et fugiat dicta nam asperiores dolore?</p>
            TXT
        ));

        $manager->persist($this->createCmsBlock('vote-statuses-voting-platform-description', '', <<<TXT
            <p>Lorem ipsum dolor sit amet. Eum  facilis et nemo dicta et  sint et numquam sapiente eum commodi consequatur ut dolorem natus? Sed similique itaque in  rerum ea alias voluptates ut optio odio nam officiis deleniti aut sapiente voluptates et culpa tenetur. Vel dignissimos provident et eligendi officia in animi distinctio est omnis esse vel cumque doloribus eos cumque nobis. </p><p>Qui saepe nobis et nulla natus aut enim unde est voluptatem expedita aut unde vero quo necessitatibus rerum. Vel cumque autem non corrupti ipsa sit saepe dignissimos ut exercitationem molestiae non dolores alias hic alias molestiae ut optio ipsum. </p><p>In quae accusantium et facere galisum aut repellat voluptatem sit iure autem qui iusto aliquid hic ratione ratione. Ut aspernatur nulla aut dolorum nobis ea vitae quam ad ipsa commodi. A quia voluptas ut itaque officiis sed asperiores distinctio qui quis tenetur aut quia velit et fugiat dicta nam asperiores dolore?</p>
            TXT
        ));

        $manager->persist($this->createCmsBlock('departmental-election-sas-rules-text', '', <<<TXT
            <p>Lorem ipsum dolor sit amet. Eum  facilis et nemo dicta et  sint et numquam sapiente eum commodi consequatur ut dolorem natus? Sed similique itaque in  rerum ea alias voluptates ut optio odio nam officiis deleniti aut sapiente voluptates et culpa tenetur. Vel dignissimos provident et eligendi officia in animi distinctio est omnis esse vel cumque doloribus eos cumque nobis. </p><p>Qui saepe nobis et nulla natus aut enim unde est voluptatem expedita aut unde vero quo necessitatibus rerum. Vel cumque autem non corrupti ipsa sit saepe dignissimos ut exercitationem molestiae non dolores alias hic alias molestiae ut optio ipsum. </p><p>In quae accusantium et facere galisum aut repellat voluptatem sit iure autem qui iusto aliquid hic ratione ratione. Ut aspernatur nulla aut dolorum nobis ea vitae quam ad ipsa commodi. A quia voluptas ut itaque officiis sed asperiores distinctio qui quis tenetur aut quia velit et fugiat dicta nam asperiores dolore?</p>
            TXT
        ));

        $manager->persist($object = $this->createCmsBlock('local-election-welcome-page', '', <<<TXT
            <p>**Lorem ipsum** dolor sit amet. Eum  facilis et nemo dicta et  sint et numquam sapiente eum commodi consequatur ut dolorem natus? Sed similique itaque in  rerum ea alias voluptates ut optio odio nam officiis deleniti aut sapiente voluptates et culpa tenetur. Vel dignissimos provident et eligendi officia in animi distinctio est omnis esse vel cumque doloribus eos cumque nobis. </p><p>Qui saepe nobis et nulla natus aut enim unde est voluptatem expedita aut unde vero quo necessitatibus rerum. Vel cumque autem non corrupti ipsa sit saepe dignissimos ut exercitationem molestiae non dolores alias hic alias molestiae ut optio ipsum. </p><p>In quae accusantium et facere galisum aut repellat voluptatem sit iure autem qui iusto aliquid hic ratione ratione. Ut aspernatur nulla aut dolorum nobis ea vitae quam ad ipsa commodi. A quia voluptas ut itaque officiis sed asperiores distinctio qui quis tenetur aut quia velit et fugiat dicta nam asperiores dolore?</p>
            TXT
        ));
        $this->setReference('cms-block-local-election-welcome-page', $object);

        $manager->persist($object = $this->createCmsBlock('cms-block-territorial-assembly-election-welcome-page', '', <<<TXT
            <p>**Lorem ipsum** dolor sit amet. Eum  facilis et nemo dicta et  sint et numquam sapiente eum commodi consequatur ut dolorem natus? Sed similique itaque in  rerum ea alias voluptates ut optio odio nam officiis deleniti aut sapiente voluptates et culpa tenetur. Vel dignissimos provident et eligendi officia in animi distinctio est omnis esse vel cumque doloribus eos cumque nobis. </p><p>Qui saepe nobis et nulla natus aut enim unde est voluptatem expedita aut unde vero quo necessitatibus rerum. Vel cumque autem non corrupti ipsa sit saepe dignissimos ut exercitationem molestiae non dolores alias hic alias molestiae ut optio ipsum. </p><p>In quae accusantium et facere galisum aut repellat voluptatem sit iure autem qui iusto aliquid hic ratione ratione. Ut aspernatur nulla aut dolorum nobis ea vitae quam ad ipsa commodi. A quia voluptas ut itaque officiis sed asperiores distinctio qui quis tenetur aut quia velit et fugiat dicta nam asperiores dolore?</p>
            TXT
        ));
        $this->setReference('cms-block-territorial-assembly-election-welcome-page', $object);

        $manager->persist($object = $this->createCmsBlock('cms-block-national-consultation-welcome-page', '', <<<TXT
            <p>**Lorem ipsum** dolor sit amet. Eum  facilis et nemo dicta et  sint et numquam sapiente eum commodi consequatur ut dolorem natus? Sed similique itaque in  rerum ea alias voluptates ut optio odio nam officiis deleniti aut sapiente voluptates et culpa tenetur. Vel dignissimos provident et eligendi officia in animi distinctio est omnis esse vel cumque doloribus eos cumque nobis. </p><p>Qui saepe nobis et nulla natus aut enim unde est voluptatem expedita aut unde vero quo necessitatibus rerum. Vel cumque autem non corrupti ipsa sit saepe dignissimos ut exercitationem molestiae non dolores alias hic alias molestiae ut optio ipsum. </p><p>In quae accusantium et facere galisum aut repellat voluptatem sit iure autem qui iusto aliquid hic ratione ratione. Ut aspernatur nulla aut dolorum nobis ea vitae quam ad ipsa commodi. A quia voluptas ut itaque officiis sed asperiores distinctio qui quis tenetur aut quia velit et fugiat dicta nam asperiores dolore?</p>
            TXT
        ));
        $this->setReference('cms-block-national-consultation-welcome-page', $object);

        $manager->persist($object = $this->createCmsBlock('local-poll-election-welcome-page', '', <<<TXT
            <p>**Lorem ipsum** dolor sit amet. Eum  facilis et nemo dicta et  sint et numquam sapiente eum commodi consequatur ut dolorem natus? Sed similique itaque in  rerum ea alias voluptates ut optio odio nam officiis deleniti aut sapiente voluptates et culpa tenetur. Vel dignissimos provident et eligendi officia in animi distinctio est omnis esse vel cumque doloribus eos cumque nobis. </p><p>Qui saepe nobis et nulla natus aut enim unde est voluptatem expedita aut unde vero quo necessitatibus rerum. Vel cumque autem non corrupti ipsa sit saepe dignissimos ut exercitationem molestiae non dolores alias hic alias molestiae ut optio ipsum. </p><p>In quae accusantium et facere galisum aut repellat voluptatem sit iure autem qui iusto aliquid hic ratione ratione. Ut aspernatur nulla aut dolorum nobis ea vitae quam ad ipsa commodi. A quia voluptas ut itaque officiis sed asperiores distinctio qui quis tenetur aut quia velit et fugiat dicta nam asperiores dolore?</p>
            TXT
        ));
        $this->setReference('cms-block-local-poll-welcome-page', $object);

        $manager->persist($this->createCmsBlock(
            'renaissance-donation-rgpd-block',
            'Affiché en bas du formulaire d\'acceptation des mentions légales des dons',
            <<<'TXT'
                <p class="mt-4">
                    Les données recueillies sur ce formulaire sont traitées par Renaissance afin de gérer les informations relatives aux donateurs du mouvement et de permettre à Renaissance de vous envoyer des communications politiques.
                    Les informations marquées d’un astérisque sont obligatoires. L’absence de réponse dans ces champs ne permettra pas à Renaissance de traiter votre demande.
                    Conformément à la règlementation, vous disposez d’un droit d’opposition et d’un droit à la limitation du traitement de données vous concernant, ainsi que d’un droit d’accès, de rectification, de portabilité et d’effacement de vos données. Vous disposez également de la faculté de donner des directives sur le sort de vos données après votre décès.
                    Vous pouvez exercer vos droits en nous adressant votre demande accompagnée d’une copie de votre pièce d’identité à l’adresse postale ou électronique suivante : Renaissance, 68 rue du Rocher, 75008 Paris, France et <a href="mailto:mes-donnees@en-marche.fr">mes-donnees@en-marche.fr</a>
                    <br /><br />
                    Ces dons sont versés à l’AFEMA (Association de financement de l’association La République En Marche agréée le 7 Mars 2016 sous le n°1158) au bénéfice du parti politique Renaissance (Numéro RNA W943004354).
                    <br /><br />
                    Aux termes de l’article 11-4 de la loi n° 88-227 du 11 mars 1988 relative à la transparence financière de la vie politique modifiée par la loi n° 2017-286 du 6 mars 2017 : « les dons consentis et les cotisations versées en qualité d’adhérent d’un ou de plusieurs partis politiques par une personne physique dûment identifiée à une ou plusieurs associations agréées en qualité d’association de financement ou à un ou plusieurs mandataires financiers d’un ou de plusieurs partis politiques ne peuvent annuellement excéder 7 500 euros. […] Les personnes morales à l’exception des partis ou groupements politiques ne peuvent contribuer au financement des partis ou groupements politiques, ni en consentant des dons, sous quelque forme que ce soit, à leurs associations de financement ou à leurs mandataires financiers, ni en leur fournissant des biens, services ou autres avantages directs ou indirects à des prix inférieurs à ceux qui sont habituellement pratiqués. ».
                    L'article 11-5 de la même loi précise que les personnes qui ont versé un don ou consenti un prêt à un ou plusieurs partis ou groupements politiques en violation des articles 11-3-1 et 11-4 sont punies de trois ans d'emprisonnement et de 45 000 euros d'amende.
                </p>
                TXT
        ));

        $manager->persist($this->createCmsBlock('vote-statuses-resolution-title-1', 'Vote des statuts', 'Approuvez vous la résolution A ?'));
        $manager->persist($this->createCmsBlock('vote-statuses-resolution-title-2', 'Vote des statuts', 'Approuvez vous la résolution B ?'));
        $manager->persist($this->createCmsBlock('vote-statuses-resolution-title-3', 'Vote des statuts', 'Approuvez vous la résolution C ?'));

        $manager->persist($this->createCmsBlock(
            'renaissance-elected-representative-contribution-step-1',
            'Texte affiché à la première étape du funnel des cotisations élus',
            'En application de l’article 4.1.2 du Règlement Intérieur, les élus titulaires de mandats électifs locaux ouvrant droit à indemnisation doivent s’acquitter d’une cotisation mensuelle dont le montant est fixé suivant le barème décidé par le Bureau Exécutif du 28 novembre 2022: ce barème repose sur le montant des indemnités brutes perçues par l’adhérent élu.'
        ));
        $manager->persist($this->createCmsBlock(
            'renaissance-elected-representative-contribution-step-2',
            'Texte affiché à la deuxième étape du funnel des cotisations élus',
            'La cotisation calculée est égale à 2% du montant de l’ensemble des indemnités brutes perçues par l’adhérent, avec un plafond fixé à 200€ et un seuil de déclenchement à partir de 250€ d’indemnités mensuelles brutes.<br><a href="#" target="_blank" class="inline-flex items-center text-re-blue-500 text-base leading-5 underline hover:text-re-blue-600 font-medium hover:decoration-re-blue-600 hover:decoration-2">Décision complète du BUREX (PDF)</a>'
        ));
        $manager->persist($this->createCmsBlock(
            'renaissance-elected-representative-contribution-step-3',
            'Texte affiché à la troisième étape du funnel des cotisations élus',
            'Les informations suivantes (Nom, Prénom, Pays de résidence, Adresse postale, Email) doivent être renseignées et à jour dans vos informations générales. Sans cette correspondance, votre contribution pourrait être mal traitée.'
        ));
        $manager->persist($this->createCmsBlock(
            'renaissance-elected-representative-contribution-step-confirmation',
            'Texte affiché à la dernière étape du funnel des cotisations élus',
            '<p class="text-green-700">Votre cotisation a été enregistrée avec succès.</p>'
        ));

        $manager->flush();
    }

    private function createCmsBlock(
        string $name,
        string $description,
        string $content = self::MARKDOWN_CONTENT,
    ): CmsBlock {
        return new CmsBlock($name, $description, $content);
    }
}
