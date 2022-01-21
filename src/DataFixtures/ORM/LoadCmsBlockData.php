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
            'je-mengage-rgpd',
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
