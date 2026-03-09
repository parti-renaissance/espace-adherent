<?php

declare(strict_types=1);

namespace App\Controller\Api\Adherent;

use App\Entity\Adherent;
use App\Entity\Projection\ManagedUser;
use App\History\UserActionHistoryHandler;
use App\Repository\AdherentRepository;
use App\Security\Voter\ManagedUserVoter;
use App\Utils\PhoneNumberUtils;
use libphonenumber\PhoneNumberFormat;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class GetSensitiveDataController extends AbstractController
{
    private const ALLOWED_TYPES = ['phone', 'email', 'address'];

    public function __construct(
        private readonly UserActionHistoryHandler $historyHandler,
        private readonly AdherentRepository $adherentRepository,
    ) {
    }

    public function __invoke(#[CurrentUser] $user, Request $request, ManagedUser $managedUser): JsonResponse
    {
        $adherent = $this->adherentRepository->findOneByUuid($managedUser->getAdherentUuid());

        if (!$adherent instanceof Adherent) {
            throw $this->createNotFoundException('Adherent not found');
        }

        $this->denyAccessUnlessGranted(ManagedUserVoter::IS_MANAGED_USER, $adherent);

        $type = $this->validateAndExtractType($request);
        $value = $this->getSensitiveValue($managedUser, $type);

        $this->historyHandler->createSensitiveDataAccess($user, $adherent, $type);

        return new JsonResponse([$type => $value]);
    }

    private function validateAndExtractType(Request $request): string
    {
        $data = json_decode($request->getContent(), true);

        if (!\is_array($data) || !isset($data['type'])) {
            throw new BadRequestHttpException('Missing "type" parameter');
        }

        $type = $data['type'];

        if (!\in_array($type, self::ALLOWED_TYPES, true)) {
            throw new BadRequestHttpException(\sprintf('Invalid type "%s". Allowed types: %s', $type, implode(', ', self::ALLOWED_TYPES)));
        }

        return $type;
    }

    private function getSensitiveValue(ManagedUser $managedUser, string $type): mixed
    {
        return match ($type) {
            'phone' => $managedUser->getPhone()
                ? PhoneNumberUtils::format($managedUser->getPhone(), PhoneNumberFormat::E164)
                : null,
            'email' => $managedUser->getEmail(),
            'address' => [
                'address' => $managedUser->getAddress(),
                'postal_code' => $managedUser->getPostalCode(),
                'city' => $managedUser->getCity(),
                'country' => $managedUser->getCountry(),
            ],
        };
    }
}
