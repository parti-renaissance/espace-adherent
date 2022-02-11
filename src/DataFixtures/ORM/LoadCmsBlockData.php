<?php

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
            sprintf('chart-%s', AdherentCharterTypeEnum::TYPE_PHONING_CAMPAIGN),
            'Charte pour les appelants'
        ));

        $manager->persist($this->createCmsBlock(
            sprintf('chart-%s', AdherentCharterTypeEnum::TYPE_PAP_CAMPAIGN),
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
ou à <a href="mailto:mobilisation@em-2022.fr">mobilisation@em-2022.fr</a>.
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
        <li>Vous recevrez un mail identique vous indiquant vos coordonnées respectives.</li>
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
        <li>Quand nous l’avons trouvé, nous vous envoyons à tous les deux le même mail avec vos coordonnées respectives.</li>
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
        $manager->flush();
    }

    private function createCmsBlock(
        string $name,
        string $description,
        string $content = self::MARKDOWN_CONTENT
    ): CmsBlock {
        return new CmsBlock($name, $description, $content);
    }
}
