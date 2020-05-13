<?php

namespace App\Summary;

use App\Entity\Adherent;
use App\Entity\MemberSummary\JobExperience;
use App\Entity\MemberSummary\Language;
use App\Entity\MemberSummary\Training;
use App\Entity\Summary;
use App\Repository\SummaryRepository;
use Doctrine\Common\Persistence\ObjectManager;
use League\Flysystem\Filesystem;
use League\Glide\Server;
use League\Glide\Signatures\SignatureFactory;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SummaryManager
{
    const DELETE_EXPERIENCE_TOKEN = 'delete_summary_experience';
    const DELETE_TRAINING_TOKEN = 'delete_summary_training';
    const DELETE_LANGUAGE_TOKEN = 'delete_summary_language';
    const DELETE_PHOTO_TOKEN = 'delete_summary_photo';

    private $factory;
    private $repository;
    private $manager;
    private $router;
    private $storage;
    private $glide;
    private $signKey;

    public function __construct(
        SummaryFactory $factory,
        SummaryRepository $repository,
        ObjectManager $manager,
        UrlGeneratorInterface $router,
        Filesystem $storage,
        Server $glide,
        string $signKey
    ) {
        $this->factory = $factory;
        $this->repository = $repository;
        $this->manager = $manager;
        $this->router = $router;
        $this->storage = $storage;
        $this->glide = $glide;
        $this->signKey = $signKey;
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

    public function publishSummary(Summary $summary): void
    {
        $summary->publish();
        $this->updateSummary($summary);
    }

    public function unpublishSummaryForAdherent(Adherent $adherent): bool
    {
        $summary = $this->getForAdherent($adherent);

        if ($summary->unpublish()) {
            $this->updateSummary($summary);

            return true;
        }

        return false;
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

    public function setUrlProfilePicture(Summary $summary): void
    {
        if ($summary->hasPictureUploaded()) {
            $cache = Uuid::uuid4()->toString();
            $signature = SignatureFactory::create($this->signKey)->generateSignature($summary->getPicturePath(), ['cache' => $cache]);

            $summary->setUrlProfilePicture($this->router->generate('asset_url', [
                'path' => $summary->getPicturePath(),
                's' => $signature,
                'cache' => $cache,
            ], UrlGeneratorInterface::ABSOLUTE_PATH));
        }
    }

    public function removePhoto(Summary $summary): bool
    {
        try {
            // Delete profile picture from cloud storage
            $this->storage->delete($summary->getPicturePath());
            $this->glide->deleteCache($summary->getPicturePath());

            $summary->setPictureUploaded(false);
            $this->updateSummary($summary);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }
}
