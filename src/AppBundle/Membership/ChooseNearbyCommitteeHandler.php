<?php

namespace AppBundle\Membership;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Entity\CommitteeMembership;
use AppBundle\Form\MembershipChooseNearbyCommitteeType;
use AppBundle\Geocoder\Coordinates;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class ChooseNearbyCommitteeHandler
{
    private $form;
    private $manager;

    /**
     * @param FormFactoryInterface $form
     * @param EntityManager        $manager
     */
    public function __construct(FormFactoryInterface $form, EntityManager $manager)
    {
        $this->form = $form;
        $this->manager = $manager;
    }

    /**
     * Returns the form to display or null if the form don't need to be displayed (submitted and valid).
     *
     * @param Request  $request
     * @param Adherent $adherent
     *
     * @return FormInterface|null
     */
    public function handle(Request $request, Adherent $adherent): ?FormInterface
    {
        $coordinates = new Coordinates($adherent->getLatitude(), $adherent->getLongitude());

        $form = $this->form
            ->create(MembershipChooseNearbyCommitteeType::class, null, ['coordinates' => $coordinates])
            ->add('submit', SubmitType::class, ['label' => 'Terminer'])
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($form->get('committees')->getData() as $uuid) {
                if ($committee = $this->manager->getRepository(Committee::class)->findOneBy(['uuid' => $uuid])) {
                    $this->manager->persist(CommitteeMembership::createForAdherent($adherent, $committee));
                }
            }

            $this->manager->flush();

            return null;
        }

        return $form;
    }
}
