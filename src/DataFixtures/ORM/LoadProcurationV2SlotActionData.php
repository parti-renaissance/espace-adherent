<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\ProcurationV2\Proxy;
use App\Entity\ProcurationV2\ProxyAction;
use App\Entity\ProcurationV2\ProxySlot;
use App\Entity\ProcurationV2\ProxySlotAction;
use App\Entity\ProcurationV2\RequestSlot;
use App\Entity\ProcurationV2\RequestSlotAction;
use App\Procuration\V2\ProcurationActionStatusEnum;
use App\Procuration\V2\SlotActionStatusEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadProcurationV2SlotActionData extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var RequestSlot $requestSlot1 */
        $requestSlot1 = $this->getReference('request_slot_1', RequestSlot::class);
        /** @var ProxySlot $proxySlot1 */
        $proxySlot1 = $this->getReference('proxy_slot_1', ProxySlot::class);
        /** @var Adherent $matcher1 */
        $matcher1 = $this->getReference('adherent-4', Adherent::class);
        /** @var Adherent $matcher2 */
        $matcher2 = $this->getReference('adherent-8', Adherent::class);

        // Create status update history for $proxySlot1->proxy
        $proxySlot1->proxy->actions->add($this->createProxyAction(
            ProcurationActionStatusEnum::STATUS_UPDATE,
            $proxySlot1->proxy,
            new \DateTime('-10 minutes'),
            $matcher1,
            'legislative_candidate',
            [
                'old_status' => 'pending',
                'new_status' => 'excluded',
            ]
        ));
        $proxySlot1->proxy->actions->add($this->createProxyAction(
            ProcurationActionStatusEnum::STATUS_UPDATE,
            $proxySlot1->proxy,
            new \DateTime('-9 minutes'),
            $matcher2,
            'president_departmental_assembly',
            [
                'old_status' => 'excluded',
                'new_status' => 'pending',
            ]
        ));

        // Create status update history for $proxySlot1
        $proxySlot1->actions->add($this->createProxySlotAction(
            SlotActionStatusEnum::STATUS_UPDATE,
            $proxySlot1,
            new \DateTime('-8 minutes'),
            $matcher1,
            'legislative_candidate',
            [
                'old_status' => 'pending',
                'new_status' => 'manual',
            ]
        ));
        $proxySlot1->actions->add($this->createProxySlotAction(
            SlotActionStatusEnum::STATUS_UPDATE,
            $proxySlot1,
            new \DateTime('-7 minutes'),
            $matcher2,
            'president_departmental_assembly',
            [
                'old_status' => 'manual',
                'new_status' => 'pending',
            ]
        ));

        // Create match history between $requestSlot1 and $proxySlot1
        $this->createMatchHistory($requestSlot1, $proxySlot1, $matcher1, 'legislative_candidate', new \DateTime('-5 minutes'));
        $this->createUnmatchHistory($requestSlot1, $proxySlot1, $matcher1, 'legislative_candidate', new \DateTime('-4 minutes'));
        $this->createMatchHistory($requestSlot1, $proxySlot1, $matcher2, 'president_departmental_assembly', new \DateTime('-3 minutes'));

        // Match $requestSlot1 and $proxySlot1
        $requestSlot1->proxySlot = $proxySlot1;
        $proxySlot1->requestSlot = $requestSlot1;

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadProcurationV2ProxyData::class,
            LoadProcurationV2RequestData::class,
        ];
    }

    private function createProxyAction(
        ProcurationActionStatusEnum $status,
        Proxy $proxy,
        ?\DateTimeInterface $date = null,
        ?Adherent $author = null,
        ?string $authorScope = null,
        array $context = [],
    ): ProxyAction {
        $action = new ProxyAction(
            Uuid::uuid4(),
            $date ?? new \DateTime(),
            $status,
            $proxy
        );

        $action->author = $author;
        $action->authorScope = $authorScope;
        $action->context = $context;

        return $action;
    }

    private function createRequestSlotAction(
        SlotActionStatusEnum $status,
        RequestSlot $requestSlot,
        ?\DateTimeInterface $date = null,
        ?Adherent $author = null,
        ?string $authorScope = null,
        array $context = [],
    ): RequestSlotAction {
        $action = new RequestSlotAction(
            Uuid::uuid4(),
            $date ?? new \DateTime(),
            $status,
            $requestSlot
        );

        $action->author = $author;
        $action->authorScope = $authorScope;
        $action->context = $context;

        return $action;
    }

    private function createProxySlotAction(
        SlotActionStatusEnum $status,
        ProxySlot $proxySlot,
        ?\DateTimeInterface $date = null,
        ?Adherent $author = null,
        ?string $authorScope = null,
        array $context = [],
    ): ProxySlotAction {
        $action = new ProxySlotAction(
            Uuid::uuid4(),
            $date ?? new \DateTime(),
            $status,
            $proxySlot
        );

        $action->author = $author;
        $action->authorScope = $authorScope;
        $action->context = $context;

        return $action;
    }

    private function createMatchHistory(
        RequestSlot $requestSlot,
        ProxySlot $proxySlot,
        ?Adherent $author = null,
        ?string $authorScope = null,
        ?\DateTimeInterface $date = null,
    ): void {
        $requestSlot->actions->add($this->createRequestSlotAction(
            SlotActionStatusEnum::MATCH,
            $requestSlot,
            $date ?? new \DateTime(),
            $author,
            $authorScope
        ));

        $proxySlot->actions->add($this->createProxySlotAction(
            SlotActionStatusEnum::MATCH,
            $proxySlot,
            $date ?? new \DateTime(),
            $author,
            $authorScope
        ));
    }

    private function createUnmatchHistory(
        RequestSlot $requestSlot,
        ProxySlot $proxySlot,
        ?Adherent $author = null,
        ?string $authorScope = null,
        ?\DateTimeInterface $date = null,
    ): void {
        $requestSlot->actions->add($this->createRequestSlotAction(
            SlotActionStatusEnum::UNMATCH,
            $requestSlot,
            $date ?? new \DateTime(),
            $author,
            $authorScope
        ));

        $proxySlot->actions->add($this->createProxySlotAction(
            SlotActionStatusEnum::UNMATCH,
            $proxySlot,
            $date ?? new \DateTime(),
            $author,
            $authorScope
        ));
    }
}
