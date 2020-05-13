<?php

namespace App\Serializer;

use App\Entity\CitizenProject;
use App\Repository\AdherentRepository;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CitizenProjectSerializerSubscriber implements EventSubscriberInterface
{
    /** @var AdherentRepository */
    private $adherentRepository;

    /** @var UrlGeneratorInterface */
    private $urlGenerator;

    public function __construct(AdherentRepository $adherentRepository, UrlGeneratorInterface $urlGenerator)
    {
        $this->adherentRepository = $adherentRepository;
        $this->urlGenerator = $urlGenerator;
    }

    public static function getSubscribedEvents()
    {
        return [
            ['event' => 'serializer.post_serialize', 'method' => 'addProperties', 'class' => CitizenProject::class],
        ];
    }

    public function addProperties(ObjectEvent $event)
    {
        /** @var CitizenProject $citizenProject */
        $citizenProject = $event->getObject();
        $visitor = $event->getVisitor();
        $thumbnail = null;
        if ($citizenProject->getImageName()) {
            $thumbnail = $this->urlGenerator->generate('asset_url', ['path' => $citizenProject->getImagePath()], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        $visitor->addData('author', $this->adherentRepository->findOneByUuid($citizenProject->getCreatedBy())->getPartialName());
        $visitor->addData('thumbnail', $thumbnail);
    }
}
