<?php

namespace App\Controller\Api\AdherentMessage;

use App\Entity\Adherent;
use App\Entity\MyTeam\Member;
use App\MyTeam\RoleEnum;
use App\Normalizer\ImageExposeNormalizer;
use App\Repository\MyTeam\MyTeamRepository;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class GetAvailableSendersController extends AbstractController
{
    public function __invoke(
        ScopeGeneratorResolver $resolver,
        NormalizerInterface $normalizer,
        MyTeamRepository $myTeamRepository,
    ): Response {
        if (!$scope = $resolver->generate()) {
            return $this->json([]);
        }

        $defaultMemberData = [
            'instance' => $scope->getScopeInstance(),
            'role' => $scope->getMainRoleName(),
            'zone' => implode(', ', $scope->getZoneNames()) ?: null,
            'theme' => $scope->getAttribute('theme'),
        ];

        $members = [$mainUser = $scope->getMainUser()];
        if ($team = $myTeamRepository->findOneByAdherentAndScope($mainUser, $scope->getMainCode())) {
            $members = array_merge($members, $team->getMembers()->toArray());
        }

        return $this->json(array_map(static function (Member|Adherent $sender) use ($defaultMemberData, $normalizer) {
            $role = null;
            if ($sender instanceof Member) {
                $role = RoleEnum::LABELS[$sender->getRole()] ?? $sender->getRole();
                $sender = $sender->getAdherent();
            }

            return array_merge($defaultMemberData, $normalizer->normalize($sender, context: [
                'groups' => ['adherent_message_sender', ImageExposeNormalizer::NORMALIZATION_GROUP],
            ]), $role ? ['role' => $role] : []);
        }, $members));
    }
}
