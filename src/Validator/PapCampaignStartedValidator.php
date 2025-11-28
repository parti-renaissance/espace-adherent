<?php

declare(strict_types=1);

namespace App\Validator;

use App\Entity\Pap\Campaign;
use App\Repository\Pap\CampaignHistoryRepository;
use App\Repository\Pap\VotePlaceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class PapCampaignStartedValidator extends ConstraintValidator
{
    private EntityManagerInterface $entityManager;
    private CampaignHistoryRepository $campaignHistoryRepository;
    private VotePlaceRepository $votePlaceRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        CampaignHistoryRepository $campaignHistoryRepository,
        VotePlaceRepository $votePlaceRepository,
    ) {
        $this->entityManager = $entityManager;
        $this->campaignHistoryRepository = $campaignHistoryRepository;
        $this->votePlaceRepository = $votePlaceRepository;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof PapCampaignStarted) {
            throw new UnexpectedTypeException($constraint, PapCampaignStarted::class);
        }

        if (!$value) {
            return;
        }

        if (!$value instanceof Campaign) {
            throw new UnexpectedValueException($value, Campaign::class);
        }

        if (null === $value->getId()
            || !$this->campaignHistoryRepository->findBy(['campaign' => $value])) {
            return;
        }

        $oldObject = $this->entityManager->getUnitOfWork()->getOriginalEntityData($value);
        if (!$oldObject) {
            return;
        }

        if (isset($oldObject['survey'])
            && $oldObject['survey'] !== $value->getSurvey()) {
            $this
                ->context
                ->buildViolation($constraint->messageSurvey)
                ->atPath('survey')
                ->addViolation()
            ;
        }

        if (array_diff($this->votePlaceRepository->findByCampaign($value), $value->getVotePlaces()->toArray())) {
            $this
                ->context
                ->buildViolation($constraint->messageVotePlaces)
                ->atPath('votePlaces')
                ->addViolation()
            ;
        }
    }
}
