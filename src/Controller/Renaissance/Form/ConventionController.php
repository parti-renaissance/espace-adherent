<?php

declare(strict_types=1);

namespace App\Controller\Renaissance\Form;

use App\Entity\Adherent;
use App\Security\Http\Session\AnonymousFollowerSession;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;

#[Route('/formulaire/convention/{slug}', name: 'app_convention', requirements: ['slug' => '[a-zA-Z0-9\-]+'], methods: 'GET')]
#[Route('/formulaire/{slug}', name: 'app_form', requirements: ['slug' => '[a-zA-Z0-9\-]+'], methods: 'GET')]
class ConventionController extends AbstractController
{
    public const array FORMS = [
        'regalien' => [
            'title' => 'Régalien',
            'id' => 'n9MNK5',
            'convention' => true,
        ],
        'economique-et-social' => [
            'title' => 'Économique et social',
            'id' => '3qvLXd',
            'convention' => true,
        ],
        'transition-ecologique' => [
            'title' => 'Transition écologique',
            'id' => 'wav9EZ',
            'convention' => true,
        ],
        'consultation-nom' => [
            'title' => 'Consultation nom',
            'id' => 'w4VPgb',
        ],
    ];

    public function __invoke(Request $request, ?UserInterface $user, string $slug, AnonymousFollowerSession $anonymousFollowerSession): Response
    {
        if (empty(self::FORMS[$slug])) {
            throw $this->createNotFoundException();
        }

        if ($response = $anonymousFollowerSession->start($request)) {
            return $response;
        }

        return $this->render('renaissance/convention/form.html.twig', [
            'adherent_access_granted' => $user instanceof Adherent && $user->hasActiveMembership(),
            'form' => self::FORMS[$slug],
            'slug' => $slug,
        ]);
    }
}
