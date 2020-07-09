<?php

namespace App\Form;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\MyTeam\DelegatedAccess;
use App\Repository\CommitteeRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class MyReferentCommitteeChoiceType extends AbstractType
{
    private $security;
    private $repository;
    private $session;

    public function __construct(Security $security, CommitteeRepository $repository, SessionInterface $session)
    {
        $this->security = $security;
        $this->repository = $repository;
        $this->session = $session;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => $this->getChoices(),
            'class' => Committee::class,
            'choice_label' => 'name',
            'choice_value' => 'uuid',
        ]);
    }

    public function getParent()
    {
        return EntityType::class;
    }

    private function getChoices(): array
    {
        $user = $this->security->getUser();

        if (!$user || !$user instanceof Adherent) {
            return [];
        }

        if ($delegatedAccessUuid = $this->session->get(DelegatedAccess::ATTRIBUTE_KEY)) {
            $user = $user->getReceivedDelegatedAccessByUuid($delegatedAccessUuid)->getDelegator();
        }

        if (!$user->isReferent()) {
            return [];
        }

        return $this->repository->findManagedBy($user);
    }
}
