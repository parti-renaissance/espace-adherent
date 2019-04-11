<?php

namespace AppBundle\Controller\Api;

use AppBundle\Entity\Adherent;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AdherentController extends AbstractController
{
    /**
     * @Route(
     *     "/adherents/me/anonymize",
     *     name="api_adherent_anonymize_me",
     *     methods={"PUT"}
     * )
     * @Security("is_granted('ROLE_ADHERENT')")
     */
    public function anonymizeAction(
        Request $request,
        Serializer $serializer,
        ValidatorInterface $validator,
        ObjectManager $manager
    ): Response {
        $data = json_decode($request->getContent(), true);
        if (!$nickname = $data['nickname'] ?? null) {
            return new JsonResponse('Property "nickname" is required.', Response::HTTP_BAD_REQUEST);
        }

        /** @var Adherent $adherent */
        $adherent = $this->getUser();
        $adherent->setNickname($nickname);
        $adherent->setNicknameUsed($data['use_nickname'] ?? false);

        $violations = $validator->validate($adherent, null, ['anonymize']);

        if (0 === $violations->count()) {
            $manager->flush();

            return new JsonResponse(
                $serializer->serialize($adherent, 'json', ['groups' => ['idea_read']]),
                Response::HTTP_OK,
                [],
                true
            );
        }

        $errors = $serializer->serialize($violations, 'jsonproblem');

        return JsonResponse::fromJsonString($errors, Response::HTTP_BAD_REQUEST);
    }
}
