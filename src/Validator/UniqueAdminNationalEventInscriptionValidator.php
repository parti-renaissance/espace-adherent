<?php

declare(strict_types=1);

namespace App\Validator;

use App\Entity\NationalEvent\EventInscription;
use App\NationalEvent\NationalEventTypeEnum;
use App\Repository\NationalEvent\EventInscriptionRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Contracts\Translation\TranslatorInterface;

class UniqueAdminNationalEventInscriptionValidator extends ConstraintValidator
{
    public function __construct(
        private readonly EventInscriptionRepository $inscriptionRepository,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueAdminNationalEventInscription) {
            throw new UnexpectedTypeException($constraint, UniqueAdminNationalEventInscription::class);
        }

        if (!$value instanceof EventInscription) {
            throw new UnexpectedTypeException($value, EventInscription::class);
        }

        if (!$value->event || !$value->addressEmail || !$value->firstName || !$value->lastName) {
            return;
        }

        $duplicates = $this->inscriptionRepository->findAdminDuplicates(
            $value->event,
            $value->addressEmail,
            $value->firstName,
            $value->lastName,
        );

        $duplicates = array_values(array_filter($duplicates, static fn (EventInscription $i): bool => $i !== $value));
        if ([] === $duplicates) {
            return;
        }

        $routeName = NationalEventTypeEnum::JEM === $value->event->type
            ? 'admin_app_nationalevent_nationalevent_jem_inscriptions_edit'
            : 'admin_app_nationalevent_eventinscription_edit';

        $rows = [];
        foreach ($duplicates as $duplicate) {
            $rows[] = \sprintf(
                '<tr>'
                .'<td>#%s</td>'
                .'<td><strong>%s</strong></td>'
                .'<td>%s</td>'
                .'<td>%s</td>'
                .'<td><a href="%s" target="_blank" rel="noopener">voir</a></td>'
                .'</tr>',
                htmlspecialchars($duplicate->getPublicId() ?? (string) $duplicate->getId(), \ENT_QUOTES),
                htmlspecialchars($this->translator->trans($duplicate->status), \ENT_QUOTES),
                htmlspecialchars(
                    $duplicate->paymentStatus
                        ? $this->translator->trans('national_event.payment.status.'.$duplicate->paymentStatus->value)
                        : '—',
                    \ENT_QUOTES,
                ),
                htmlspecialchars($duplicate->getCreatedAt()->format('d/m/Y H:i') ?? '—', \ENT_QUOTES),
                htmlspecialchars(
                    $this->urlGenerator->generate($routeName, ['id' => $duplicate->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
                    \ENT_QUOTES,
                ),
            );
        }

        $table = '<table class="table table-condensed" style="margin-top:8px;margin-bottom:0">'
            .'<thead><tr>'
            .'<th>Inscription</th>'
            .'<th>Statut</th>'
            .'<th>Paiement</th>'
            .'<th>Inscrit le</th>'
            .'<th></th>'
            .'</tr></thead>'
            .'<tbody>'.implode('', $rows).'</tbody>'
            .'</table>';

        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ count }}', (string) \count($duplicates))
            ->setParameter('{{ table }}', $table)
            ->addViolation()
        ;
    }
}
