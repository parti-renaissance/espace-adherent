<?php

namespace App\Procuration\V2;

use App\Entity\Adherent;
use App\Entity\ProcurationV2\Proxy;
use App\Entity\ProcurationV2\ProxyAction;
use App\Entity\ProcurationV2\ProxySlot;
use App\Entity\ProcurationV2\ProxySlotAction;
use App\Entity\ProcurationV2\Request;
use App\Entity\ProcurationV2\RequestAction;
use App\Entity\ProcurationV2\RequestSlot;
use App\Entity\ProcurationV2\RequestSlotAction;
use App\Scope\ScopeGeneratorResolver;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Security\Core\Security;

class ProcurationActionHandler
{
    public function __construct(
        private readonly Security $security,
        private readonly ScopeGeneratorResolver $scopeGeneratorResolver,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function createRequestStatusUpdateAction(Request $request, RequestStatusEnum $oldStatus): void
    {
        $request->actions->add(
            $this->createRequestAction(
                ProcurationActionStatusEnum::STATUS_UPDATE,
                $request,
                $this->getAuthor(),
                $this->getAuthorScope(),
                [
                    'old_status' => $oldStatus->value,
                    'new_status' => $request->status->value,
                ]
            )
        );

        $this->entityManager->flush();
    }

    public function createProxyStatusUpdateAction(Proxy $proxy, ProxyStatusEnum $oldStatus): void
    {
        $proxy->actions->add(
            $this->createProxyAction(
                ProcurationActionStatusEnum::STATUS_UPDATE,
                $proxy,
                $this->getAuthor(),
                $this->getAuthorScope(),
                [
                    'old_status' => $oldStatus->value,
                    'new_status' => $proxy->status->value,
                ]
            )
        );

        $this->entityManager->flush();
    }

    public function createUnmatchActions(RequestSlot $requestSlot, ProxySlot $proxySlot): void
    {
        $author = $this->getAuthor();
        $authorScope = $this->getAuthorScope();

        $requestSlot->actions->add(
            $this->createRequestSlotAction(
                SlotActionStatusEnum::UNMATCH,
                $requestSlot,
                $author,
                $authorScope
            )
        );

        $proxySlot->actions->add(
            $this->createProxySlotAction(
                SlotActionStatusEnum::UNMATCH,
                $proxySlot,
                $author,
                $authorScope
            )
        );

        $this->entityManager->flush();
    }

    public function createMatchActions(RequestSlot $requestSlot, ProxySlot $proxySlot): void
    {
        $author = $this->getAuthor();
        $authorScope = $this->getAuthorScope();

        $requestSlot->actions->add(
            $this->createRequestSlotAction(
                SlotActionStatusEnum::MATCH,
                $requestSlot,
                $author,
                $authorScope
            )
        );

        $proxySlot->actions->add(
            $this->createProxySlotAction(
                SlotActionStatusEnum::MATCH,
                $proxySlot,
                $author,
                $authorScope
            )
        );

        $this->entityManager->flush();
    }

    public function createRequestSlotStatusUpdateAction(
        RequestSlot $requestSlot,
        string $newStatus,
        string $oldStatus,
    ): void {
        $requestSlot->actions->add(
            $this->createRequestSlotAction(
                SlotActionStatusEnum::STATUS_UPDATE,
                $requestSlot,
                $this->getAuthor(),
                $this->getAuthorScope(),
                [
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                ]
            )
        );
    }

    public function createProxySlotStatusUpdateAction(
        ProxySlot $proxySlot,
        string $newStatus,
        string $oldStatus,
    ): void {
        $proxySlot->actions->add(
            $this->createProxySlotAction(
                SlotActionStatusEnum::STATUS_UPDATE,
                $proxySlot,
                $this->getAuthor(),
                $this->getAuthorScope(),
                [
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                ]
            )
        );
    }

    private function createRequestAction(
        ProcurationActionStatusEnum $status,
        Request $request,
        ?Adherent $author = null,
        ?string $authorScope = null,
        array $context = [],
    ): RequestAction {
        $action = new RequestAction(
            Uuid::uuid4(),
            new \DateTime(),
            $status,
            $request
        );

        $action->author = $author;
        $action->authorScope = $authorScope;
        $action->context = $context;

        return $action;
    }

    private function createProxyAction(
        ProcurationActionStatusEnum $status,
        Proxy $proxy,
        ?Adherent $author = null,
        ?string $authorScope = null,
        array $context = [],
    ): ProxyAction {
        $action = new ProxyAction(
            Uuid::uuid4(),
            new \DateTime(),
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
        ?Adherent $author = null,
        ?string $authorScope = null,
        array $context = [],
    ): RequestSlotAction {
        $action = new RequestSlotAction(
            Uuid::uuid4(),
            new \DateTime(),
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
        ?Adherent $author = null,
        ?string $authorScope = null,
        array $context = [],
    ): ProxySlotAction {
        $action = new ProxySlotAction(
            Uuid::uuid4(),
            new \DateTime(),
            $status,
            $proxySlot
        );

        $action->author = $author;
        $action->authorScope = $authorScope;
        $action->context = $context;

        return $action;
    }

    private function getAuthor(): ?Adherent
    {
        $user = $this->security->getUser();

        return $user instanceof Adherent ? $user : null;
    }

    private function getAuthorScope(): ?string
    {
        return $this->scopeGeneratorResolver->resolve()?->getCode();
    }
}
