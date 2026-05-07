<?php

declare(strict_types=1);

namespace App\Controller\Api\AdherentMessage;

use App\AdherentMessage\AdherentMessageManager;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Campaign\Audience\AudienceMessagePreparer;
use App\Mailchimp\Campaign\Audience\SendStatusFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class SendAdherentMessageController extends AbstractController
{
    public function __invoke(
        AdherentMessageManager $manager,
        AudienceMessagePreparer $preparer,
        SendStatusFactory $sendStatusFactory,
        AdherentMessage $message,
        #[CurrentUser] Adherent $adherent,
    ): Response {
        if (!$message->isSynchronized()) {
            throw new BadRequestHttpException('The message is not yet ready to send.');
        }

        if (!$message->getSubject()) {
            throw new BadRequestHttpException('Subject is required.');
        }

        if ($message->isSent()) {
            throw new BadRequestHttpException('This message has been already sent.');
        }

        if ($adherent->sandboxMode || $message->getAuthor()?->sandboxMode || $message->getSender()?->sandboxMode) {
            throw new \RuntimeException('An error occurred. Please try again later.');
        }

        // Statutory messages bypass the Mailchimp campaign + static segment lifecycle:
        // recipients are computed from zones at send time and pushed via the transactional sender.
        if ($message->isStatutory()) {
            $manager->send($message, $manager->getRecipients($message));

            return new JsonResponse(['status' => 'sent']);
        }

        $campaign = $message->getMailchimpCampaigns()[0] ?? null;
        if (!$campaign instanceof MailchimpCampaign) {
            throw new BadRequestHttpException('No Mailchimp campaign attached to this message.');
        }

        if ($campaign->canSend() && $campaign->isAudienceFresh()) {
            $manager->send($message, $manager->getRecipients($message));

            return new JsonResponse([
                'status' => 'sent',
                'send_status' => $sendStatusFactory->build($campaign),
            ]);
        }

        $result = $preparer->prepare($message, $adherent);

        return new JsonResponse(
            $result->toApiPayload(),
            $result->isConflict() ? Response::HTTP_CONFLICT : Response::HTTP_ACCEPTED,
        );
    }
}
