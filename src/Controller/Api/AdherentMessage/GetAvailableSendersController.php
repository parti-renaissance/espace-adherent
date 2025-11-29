<?php

declare(strict_types=1);

namespace App\Controller\Api\AdherentMessage;

use App\Entity\Adherent;
use App\Entity\MyTeam\Member;
use App\Normalizer\ImageExposeNormalizer;
use App\Repository\MyTeam\MyTeamRepository;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class GetAvailableSendersController extends AbstractController
{
    public function __construct(
        private readonly ScopeGeneratorResolver $resolver,
        private readonly NormalizerInterface $normalizer,
        private readonly MyTeamRepository $myTeamRepository,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function __invoke(
    ): Response {
        if (!$scope = $this->resolver->generate()) {
            return $this->json([]);
        }

        $defaultMemberData = [
            'instance' => $scope->getScopeInstance(),
            'role' => $scope->getMainRoleName(),
            'zone' => implode(', ', $scope->getZoneNames()) ?: null,
            'theme' => $scope->getAttribute('theme'),
        ];

        $members = [$mainUser = $scope->getMainUser()];
        if ($team = $this->myTeamRepository->findOneByAdherentAndScope($mainUser, $scope->getMainCode())) {
            $members = array_merge($members, $team->getMembers()->toArray());
        }

        return $this->json(array_map(function (Member|Adherent $sender) use ($defaultMemberData) {
            $role = null;
            if ($sender instanceof Member) {
                $key = 'my_team_member.role.'.$sender->getRole();
                $role = $this->translator->trans($key, ['gender' => $sender->getAdherent()?->getGender()]);
                if ($role === $key) {
                    $role = $sender->getRole();
                }
                $sender = $sender->getAdherent();
            }

            return array_merge($defaultMemberData, $this->normalizer->normalize($sender, context: [
                'groups' => ['adherent_message_sender', ImageExposeNormalizer::NORMALIZATION_GROUP],
            ]), $role ? ['role' => $role] : []);
        }, $members));
    }
}
