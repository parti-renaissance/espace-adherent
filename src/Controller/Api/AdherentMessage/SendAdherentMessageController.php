<?php

declare(strict_types=1);

namespace App\Controller\Api\AdherentMessage;

use App\AdherentMessage\AdherentMessageManager;
use App\AdherentMessage\MailchimpStatusEnum;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Campaign\Audience\AudienceMessagePreparer;
use App\Mailchimp\Campaign\Audience\PrepareResult;
use App\Mailchimp\Campaign\Audience\SendStatusFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class SendAdherentMessageController extends AbstractController
{
    public function __invoke(
        AdherentMessageManager $manager,
        AudienceMessagePreparer $preparer,
        SendStatusFactory $sendStatusFactory,
        LockFactory $lockFactory,
        AdherentMessage $message,
        #[CurrentUser] Adherent $adherent,
    ): Response {
        if (!$message->isSynchronized()) {
            throw new BadRequestHttpException('The message is not yet ready to send.');
        }

        if (!$message->getSubject()) {
            throw new BadRequestHttpException('Subject is required.');
        }

        if ($adherent->sandboxMode || $message->getAuthor()?->sandboxMode || $message->getSender()?->sandboxMode) {
            throw new \RuntimeException('An error occurred. Please try again later.');
        }

        if ($message->isStatutory()) {
            if ($message->isSent()) {
                throw new BadRequestHttpException('This message has been already sent.');
            }

            $manager->send($message, $manager->getRecipients($message));

            return new JsonResponse(['status' => 'sent']);
        }

        $campaign = $message->getMailchimpCampaigns()[0] ?? null;
        if (!$campaign instanceof MailchimpCampaign) {
            throw new BadRequestHttpException('No Mailchimp campaign attached to this message.');
        }

        if (\in_array($campaign->status, [MailchimpStatusEnum::Sent, MailchimpStatusEnum::Sending], true)) {
            throw new BadRequestHttpException('This message has been already sent.');
        }

        $lock = $lockFactory->createLock(\sprintf('adherent_message_send_%d', $campaign->getId()), 30.0);
        if (!$lock->acquire()) {
            return new JsonResponse(
                PrepareResult::conflict($sendStatusFactory->build($campaign))->toApiPayload(),
                Response::HTTP_CONFLICT,
            );
        }

        try {
            $result = $preparer->prepare($message, $adherent);

            if ($result->isConflict()) {
                return new JsonResponse($result->toApiPayload(), Response::HTTP_CONFLICT);
            }

            $manager->sendPublication($message);

            return new JsonResponse([
                'status' => 'sent',
                'send_status' => $sendStatusFactory->build($campaign),
            ]);
        } finally {
            $lock->release();
        }
    }
}
