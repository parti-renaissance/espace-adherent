<?php

namespace Tests\App\EntityListener;

use App\Entity\Adherent;
use App\Entity\MyTeam\DelegatedAccess;
use App\Entity\MyTeam\MyTeam;
use App\Membership\Event\UserEvent;
use App\Membership\UserEvents;
use App\Scope\ScopeEnum;
use Tests\App\AbstractKernelTestCase;

class RevokeDelegatedAccessListenerTest extends AbstractKernelTestCase
{
    public function testDeputyDelegatedAccessAreRemovedWhenAdherentLostHisAccess()
    {
        $deputy = $this->manager->getRepository(Adherent::class)->findOneByEmail('deputy-ch-li@en-marche-dev.fr');

        $this->assertCount(3, $this->manager->getRepository(DelegatedAccess::class)->findBy(['delegator' => $deputy]));

        $this->getEventDispatcher()->dispatch(new UserEvent($deputy), UserEvents::USER_BEFORE_UPDATE);

        $deputy->removeZoneBasedRole($deputy->findZoneBasedRole(ScopeEnum::DEPUTY));
        $this->manager->flush();

        $this->getEventDispatcher()->dispatch(new UserEvent($deputy), UserEvents::USER_UPDATED);

        $this->assertCount(0, $this->manager->getRepository(DelegatedAccess::class)->findBy(['delegator' => $deputy]));
    }

    public function testSenatorDelegatedAccessAreRemovedWhenAdherentLostHisAccess()
    {
        $senator = $this->manager->getRepository(Adherent::class)->findOneByEmail('senateur@en-marche-dev.fr');

        $this->assertCount(2, $this->manager->getRepository(DelegatedAccess::class)->findBy(['delegator' => $senator]));

        $this->getEventDispatcher()->dispatch(new UserEvent($senator), UserEvents::USER_BEFORE_UPDATE);

        $senator->setSenatorArea(null);
        $this->manager->flush();

        $this->getEventDispatcher()->dispatch(new UserEvent($senator), UserEvents::USER_UPDATED);

        $this->assertCount(0, $this->manager->getRepository(DelegatedAccess::class)->findBy(['delegator' => $senator]));
    }

    public function testReferentDelegatedAccessAreRemovedWhenAdherentLostHisAccess()
    {
        $referent = $this->manager->getRepository(Adherent::class)->findOneByEmail('referent@en-marche-dev.fr');

        $this->assertCount(4, $this->manager->getRepository(DelegatedAccess::class)->findBy(['delegator' => $referent, 'type' => ScopeEnum::REFERENT]));
        $this->assertCount(1, $this->manager->getRepository(MyTeam::class)->findBy(['owner' => $referent, 'scope' => ScopeEnum::REFERENT]));

        $this->getEventDispatcher()->dispatch(new UserEvent($referent), UserEvents::USER_BEFORE_UPDATE);

        $referent->setManagedArea(null);
        $this->manager->flush();

        $this->getEventDispatcher()->dispatch(new UserEvent($referent), UserEvents::USER_UPDATED);

        $this->assertCount(0, $this->manager->getRepository(DelegatedAccess::class)->findBy(['delegator' => $referent, 'type' => ScopeEnum::REFERENT]));
        $this->assertCount(1, $this->manager->getRepository(MyTeam::class)->findBy(['owner' => $referent, 'scope' => ScopeEnum::REFERENT]));
    }
}
