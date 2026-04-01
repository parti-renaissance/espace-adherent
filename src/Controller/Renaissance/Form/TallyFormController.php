<?php

declare(strict_types=1);

namespace App\Controller\Renaissance\Form;

use App\Adherent\Tag\TagEnum;
use App\Entity\Adherent;
use App\Repository\TallyFormRepository;
use App\Security\Http\Session\AnonymousFollowerSession;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;

#[Route('/formulaire/{slug}', name: 'app_form', requirements: ['slug' => '[a-zA-Z0-9\-\/]+'], methods: 'GET')]
class TallyFormController extends AbstractController
{
    public function __invoke(
        Request $request,
        ?UserInterface $user,
        string $slug,
        AnonymousFollowerSession $anonymousFollowerSession,
        TallyFormRepository $tallyFormRepository,
    ): Response {
        $form = $tallyFormRepository->findOneBySlug($slug);

        if (!$form) {
            throw $this->createNotFoundException();
        }

        if ($response = $anonymousFollowerSession->start($request)) {
            return $response;
        }

        return $this->render('renaissance/tally_form/form.html.twig', [
            'adherent_access_granted' => $user instanceof Adherent && ($user->hasActiveMembership() || $user->hasTag(TagEnum::getAdherentYearTag(date('Y') - 1))),
            'form' => $form,
            'slug' => $slug,
        ]);
    }
}
