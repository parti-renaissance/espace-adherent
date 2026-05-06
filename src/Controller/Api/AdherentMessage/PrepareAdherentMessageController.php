<?php

declare(strict_types=1);

namespace App\Controller\Api\AdherentMessage;

use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Mailchimp\Campaign\Audience\AudienceMessagePreparer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class PrepareAdherentMessageController extends AbstractController
{
    public function __invoke(
        AudienceMessagePreparer $preparer,
        AdherentMessage $message,
        #[CurrentUser] Adherent $user,
    ): JsonResponse {
        $result = $preparer->prepare($message, $user);

        return new JsonResponse(
            $result->toApiPayload(),
            match (true) {
                $result->isConflict() => Response::HTTP_CONFLICT,
                $result->isPreparing() => Response::HTTP_ACCEPTED,
                default => Response::HTTP_OK,
            },
        );
    }
}
