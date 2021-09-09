<?php

namespace App\Controller\Api\Phoning;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/v3/phoning_campaigns/tutorial", name="api_get_phoning_campaigns_tutorial", methods={"GET"})
 *
 * @Security("is_granted('ROLE_PHONING_CAMPAIGN_MEMBER')")
 */
class GetPhoningCampaignTutorialController extends AbstractController
{
    public function __invoke(): JsonResponse
    {
        return $this->json(['content' => <<<TUTORIAL
# Conseils pour réussir ses appels

Toujours rester courtois et souriant

Peu importe le comportement de la personne à l’autre bout du téléphone, en tant que membre de l’équipe de La République En Marche, soyez polis et écourter l’échange pour éviter l’invective.

## Ne pas insister

Insister avec une personne qui émet plusieurs objections ou entre dans un débat contradictoire est contre-productif, mieux vaut finir cordialement d’un commun accord que perdre le temps précieux qui vous permettra de mobiliser d’autres personnes.

## Bien assurer son suivi d’appel (fichier individuel)

Pour éviter d’être plusieurs à appeler par erreur les mêmes numéros, ou rappeler quelqu’un qui a déjà été contacté, mieux vaut s’assurer d’être en possession d’une liste qui n’a pas encore été prise en charge.

Choisissez un endroit où les conditions de phoning seront bonnes ; évitez les endroits bruyants ou les dérangements récurrents. Ne pas se laisser distraire par son environnement.

Écoute active : indispensable à l’accompagnement de l’interlocuteur.

Téléphoner plutôt sur un créneau horaire entre 9h et 19h, du lundi au samedi.

Ne pas parler trop lentement (sensation de perte de temps), ou trop vite (risque de faire répéter), s’adapter au débit de l’interlocuteur et adapter le ton de la voix.

Ne pas parler trop fort, ne pas couper la parole

Toujours rester courtois et souriant

Peu importe le comportement de la personne à l’autre bout du téléphone, en tant que membre de l’équipe de La République En Marche, soyez polis et écourter l’échange pour éviter l’invective.

Ne pas insister

Insister avec une personne qui émet plusieurs objections ou entre dans un débat contradictoire est contre-productif, mieux vaut finir cordialement d’un commun accord que perdre le temps précieux qui vous permettra de mobiliser d’autres personnes.

**CONCLUSION :** Remerciements, salutations, toujours raccrocher après l’interlocuteur

# Les situations que vous pourriez rencontrer

## Je n’ai pas le temps !

Puis-je vous rappeler plus tard ?

## Je suis déçu(e) par LaREM/Macron

Si vous sentez que la personne est ouverte à la discussion, vous pouvez éventuellement poser la question « Y a-t-il une raison particulière à cette déception, une mesure du Gouvernement en particulier ? »

En tant qu’animateur local êtes-vous en lien avec vos parlementaires, leur faites-vous part de votre mécontentement ?

Sinon, n’insistez pas ! « Je comprends, je ne vous dérange pas plus et vous souhaite une bonne journée »

## Je souhaite quitter LaREM

Si la personne ne veut plus être AL et vous demande de désadhérer, elle doit d’abord démissionner de ses fonctions d’animateur/animatrice local(e) en écrivant à [jemarche@en-marche.fr](mailto:jemarche@en-marche.fr)

## Je n’ai pas/plus le temps de m’engager

Si la personne souhaite ne plus être Als, elle doit démissionner de ses fonctions d’animateur/animatrice local(e) en écrivant à [jemarche@en-marche.fr](mailto:jemarche@en-marche.fr%E2%80%AF)

## La personne au bout du fil est visiblement dans une grande détresse psychologique, que puis-je faire ?

Vous pouvez jouer le rôle de veille et d’alerte mais vous n’êtes pas psychologue, ce n’est ni votre rôle ni votre responsabilité.

N’hésitez pas à indiquer à votre interlocuteur les contacts appropriés pour être aidé. Ils sont tous disponibles ici : [numéros utiles](https://storage.googleapis.com/en-marche-fr/COMMUNICATION/LaREM-Numeros-utiles-COVID19.pdf). Si besoin, discutez-en avec le référent ou le coordinateur régional pour voir comment nous pourrions aider la personne.

Enfin, gardez à l’esprit qu’un échange sympa est déjà̀ une manière d’aider et d’être présent !

## La discussion m’a complétement échappée et je ne sais pas comment reprendre la main, comment faire ?

Sentez-vous libre de reprendre le contrôle de la discussion quand vous l’estimez nécessaire.

Faites-le toujours avec empathie et sans sauter complètement du coq à l’âne mais fermement grâce à des transitions douces du type : « je comprends très bien / je note ce que vous me dites / nous avons eu d’autres témoignages qui allaient dans ce sens mais on remarque également que ou que pensez-vous d’ailleurs de... » A noter que les gens prennent moins ombrage d’une parole coupée si on la leur redonne ensuite.

Si vraiment la situation s’éternise vous pouvez également recentrer l’appel clairement « l’enjeu de l’appel c’est avant tout de... ».

## La personne au bout du fil multiplie des questions auxquelles je n’ai pas de réponses, que puis-je faire ?

Il faut d’emblée sortir de cette attente : tenez-vous en aux informations et aux sources officielles dont vous pourrez rappeler les liens (c’est également l’occasion de prévenir la désinformation) et recentrer là aussi l’objectif de l’appel : « Je comprends vos attentes en la matière, nous sommes tous dans le même cas mais je n’en sais pas plus que vous et l’objet de cet appel c’est avant tout de ... ».

## La personne au bout du fil est très énervée et je deviens la cible de ses attaques, comment faire ?

Bien distinguer votre rôle du rôle officiel du gouvernement ou du Président de la République.

Comme pour la question précédente, n’hésitez pas à recentrer la mission de l’appel, sur un enjeu de solidarité́ par exemple : « Je comprends bien, la situation est difficile pour tout le monde mais cela nous paraissait important de prendre de vos nouvelles, de vous  apporter peut-être des éléments de réponse pour agir concrètement » etc.

Si la personne est vraiment virulente, soyez ferme, protégez-vou  et si c’est insoutenable, terminez la discussion : « Je suis navré, l’enjeu de l’appel n’est pas là et je ne suis pas le bon interlocuteur, je vous souhaite bon courage ».
TUTORIAL
        ]);
    }
}
