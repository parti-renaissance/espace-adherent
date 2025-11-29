<?php

declare(strict_types=1);

namespace Tests\App\EntityListener;

use App\Entity\Adherent;
use App\Entity\MyTeam\DelegatedAccess;
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

        $deputy->removeZoneBasedRole($deputy->findZoneBasedRole(ScopeEnum::DEPUTY));
        $this->manager->flush();

        $this->getEventDispatcher()->dispatch(new UserEvent($deputy), UserEvents::USER_UPDATED_IN_ADMIN);

        $this->assertCount(0, $this->manager->getRepository(DelegatedAccess::class)->findBy(['delegator' => $deputy]));
    }
}
