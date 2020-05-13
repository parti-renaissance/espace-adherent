<?php

namespace App\Form\EventListener;

use App\Repository\SkillRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class SkillListener implements EventSubscriberInterface
{
    private $skillRepository;

    public function __construct(SkillRepository $skillRepository)
    {
        $this->skillRepository = $skillRepository;
    }

    public static function getSubscribedEvents()
    {
        return [
            FormEvents::SUBMIT => 'onSubmit',
        ];
    }

    public function onSubmit(FormEvent $event)
    {
        $object = $event->getForm()->getData();
        $skills = $event->getData()->getSkills();

        foreach ($skills as $skill) {
            if ($skill->getId()) {
                continue;
            }

            if ($existingSkill = $this->skillRepository->findOneBy(['name' => $skill->getName()])) {
                $object->replaceSkill($skill, $existingSkill);
            }
        }
    }
}
