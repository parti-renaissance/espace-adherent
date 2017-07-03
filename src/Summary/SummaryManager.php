<?php

namespace AppBundle\Summary;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\MemberSummary\JobExperience;
use AppBundle\Entity\MemberSummary\Language;
use AppBundle\Entity\MemberSummary\Training;
use AppBundle\Entity\Summary;
use AppBundle\Repository\SummaryRepository;
use Doctrine\Common\Persistence\ObjectManager;

class SummaryManager
{
    const DELETE_EXPERIENCE_TOKEN = 'delete_summary_experience';
    const DELETE_TRAINING_TOKEN = 'delete_summary_training';
    const DELETE_LANGUAGE_TOKEN = 'delete_summary_language';

    private $factory;
    private $repository;
    private $manager;

    public function __construct(SummaryFactory $factory, SummaryRepository $repository, ObjectManager $manager)
    {
        $this->factory = $factory;
        $this->repository = $repository;
        $this->manager = $manager;
    }

    public function getForAdherent(Adherent $adherent): Summary
    {
        if ($summary = $this->repository->findOneForAdherent($adherent)) {
            return $summary;
        }

        return $this->factory->createFromAdherent($adherent);
    }

    public function updateSummary(Summary $summary): void
    {
        if (!$summary->getId()) {
            $this->manager->persist($summary);
        }

        $this->manager->flush();
    }

    public function updateExperiences(Summary $summary, JobExperience $experience): void
    {
        $summary->addExperience($experience);
        $this->updateSummary($summary);
    }

    public function removeExperience(Adherent $adherent, JobExperience $experience): bool
    {
        if (!$summary = $this->repository->findOneForAdherent($adherent)) {
            return false;
        }

        SummaryItemDisplayOrderer::removeItem($summary->getExperiences(), $experience);

        $summary->removeExperience($experience);
        $this->updateSummary($summary);

        return true;
    }

    public function updateTrainings(Summary $summary, Training $training): void
    {
        $summary->addTraining($training);
        $this->updateSummary($summary);
    }

    public function removeTraining(Adherent $adherent, Training $training): bool
    {
        if (!$summary = $this->repository->findOneForAdherent($adherent)) {
            return false;
        }

        SummaryItemDisplayOrderer::removeItem($summary->getTrainings(), $training);

        $summary->removeTraining($training);
        $this->updateSummary($summary);

        return true;
    }

    public function updateLanguages(Adherent $adherent, Language $language): void
    {
        $summary = $this->getForAdherent($adherent);

        $summary->addLanguage($language);
        $this->updateSummary($summary);
    }

    public function removeLanguage(Adherent $adherent, Language $language): bool
    {
        if (!$summary = $this->repository->findOneForAdherent($adherent)) {
            return false;
        }

        $summary->removeLanguage($language);
        $this->updateSummary($summary);

        return true;
    }
}
