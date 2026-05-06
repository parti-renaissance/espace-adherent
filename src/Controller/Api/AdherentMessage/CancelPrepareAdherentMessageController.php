<?php

declare(strict_types=1);

namespace App\Controller\Api\AdherentMessage;

use App\Entity\AdherentMessage\AdherentMessage;
use App\Mailchimp\Campaign\Audience\AudienceMessagePreparer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class CancelPrepareAdherentMessageController extends AbstractController
{
    public function __invoke(
        AudienceMessagePreparer $preparer,
        AdherentMessage $message,
    ): JsonResponse {
        $preparer->requestCancellation($message);

        return new JsonResponse(['cancelled' => true]);
    }
}
