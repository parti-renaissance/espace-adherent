<?php

namespace App\Controller\Api\Coalition;

use ApiPlatform\Core\Problem\Serializer\ConstraintViolationListNormalizer;
use App\Coalition\CauseFollowerChangeEvent;
use App\Coalition\Events;
use App\Coalition\MessageNotifier;
use App\Entity\Coalition\Cause;
use App\Entity\Coalition\CauseFollower;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @Route("/causes/{uuid}/follower", name="api_follow_cause_as_anonymous", methods={"PUT"})
 */
class CauseAnonymousFollowerController extends AbstractController
{
    public function __invoke(
        Request $request,
        Cause $cause,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        EntityManagerInterface $entityManager,
        MessageNotifier $notifier,
        EventDispatcherInterface $eventDispatcher
    ): Response {
        /** @var CauseFollower $causeFollower */
        $causeFollower = $serializer->deserialize($request->getContent(), CauseFollower::class, JsonEncoder::FORMAT);
        $causeFollower->setCause($cause);

        $errors = $validator->validate($causeFollower, null, ['Default', 'anonymous_follower']);

        if (0 === $errors->count()) {
            $entityManager->persist($causeFollower);
            $entityManager->flush();

            $notifier->sendCauseFollowerAnonymousConfirmationMessage($causeFollower);
            $eventDispatcher->dispatch(
                new CauseFollowerChangeEvent($cause),
                Events::CAUSE_FOLLOWER_ADDED
            );

            return $this->json('OK');
        }

        return JsonResponse::fromJsonString(
            $serializer->serialize($errors, ConstraintViolationListNormalizer::FORMAT),
            Response::HTTP_BAD_REQUEST
        );
    }
}
