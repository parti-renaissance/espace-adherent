<?php

declare(strict_types=1);

namespace App\Controller\Api\Adherent;

use App\Entity\Adherent;
use App\History\UserActionHistoryHandler;
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

    public function __construct(private readonly UserActionHistoryHandler $historyHandler)
    {
    }

    public function __invoke(#[CurrentUser] $user, Request $request, Adherent $adherent): JsonResponse
    {
        $this->denyAccessUnlessGranted('MANAGE_ZONEABLE_ITEM__FOR_SCOPE', $adherent);

        $type = $this->validateAndExtractType($request);
        $value = $this->getSensitiveValue($adherent, $type);

        $this->historyHandler->createSensitiveDataAccess($user, $adherent, $type);

        return $this->json([$type => $value]);
    }

    private function validateAndExtractType(Request $request): string
    {
        $type = $request->query->get('type');

        if (!$type) {
            throw new BadRequestHttpException('Missing "type" parameter');
        }

        if (!\in_array($type, self::ALLOWED_TYPES, true)) {
            throw new BadRequestHttpException(\sprintf('Invalid type "%s". Allowed types: %s', $type, implode(', ', self::ALLOWED_TYPES)));
        }

        return $type;
    }

    private function getSensitiveValue(Adherent $adherent, string $type): mixed
    {
        return match ($type) {
            'phone' => $adherent->getPhone()
                ? PhoneNumberUtils::format($adherent->getPhone(), PhoneNumberFormat::E164)
                : null,
            'email' => $adherent->getEmailAddress(),
            'address' => [
                'address' => $adherent->getAddress(),
                'postal_code' => $adherent->getPostalCode(),
                'city' => $adherent->getCityName(),
                'country' => $adherent->getCountry(),
            ],
        };
    }
}
