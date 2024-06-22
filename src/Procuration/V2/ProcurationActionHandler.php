<?php

namespace App\Procuration\V2;

use App\Entity\Adherent;
use App\Entity\ProcurationV2\ProxySlot;
use App\Entity\ProcurationV2\ProxySlotAction;
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

    private function createRequestSlotAction(
        SlotActionStatusEnum $status,
        RequestSlot $requestSlot,
        ?Adherent $author = null,
        ?string $authorScope = null,
        array $context = []
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
        array $context = []
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
