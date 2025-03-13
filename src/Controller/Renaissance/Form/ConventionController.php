<?php

namespace App\Controller\Renaissance\Form;

use App\Adherent\Tag\TagEnum;
use App\Entity\Adherent;
use App\Security\Http\Session\AnonymousFollowerSession;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;

#[Route('/formulaire/convention/{slug}', name: 'app_convention', requirements: ['slug' => '[a-zA-Z0-9\-]+'], methods: 'GET')]
class ConventionController extends AbstractController
{
    public const array CONVENTION = [
        'regalien' => [
            'title' => 'Régalien',
            'id' => 'n9MNK5',
        ],
        'economique-et-social' => [
            'title' => 'Économique et social',
            'id' => '3qvLXd',
        ],
        'transition-ecologique' => [
            'title' => 'Transition écologique',
            'id' => 'wav9EZ',
        ],
    ];

    public function __invoke(Request $request, ?UserInterface $user, string $slug, AnonymousFollowerSession $anonymousFollowerSession): Response
    {
        if (empty(self::CONVENTION[$slug])) {
            throw $this->createNotFoundException();
        }

        if ($response = $anonymousFollowerSession->start($request)) {
            return $response;
        }

        $currentYear = date('Y');

        $accessGranted = $user instanceof Adherent && (
            $user->hasTag(TagEnum::getAdherentYearTag($currentYear - 1))
            || $user->hasTag(TagEnum::getAdherentYearTag($currentYear))
        );

        return $this->render('renaissance/convention/form.html.twig', [
            'adherent_access_granted' => $accessGranted,
            'convention' => self::CONVENTION[$slug],
            'slug' => $slug,
        ]);
    }
}
