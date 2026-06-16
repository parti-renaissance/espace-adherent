<?php

declare(strict_types=1);

namespace App\Formation\Moodle\Repair;

/**
 * Decides, for a single adherent, whether their Moodle accounts show the
 * "email change created a duplicate" pattern and which account to keep.
 *
 * Pure logic: no I/O, fully driven by the Moodle account records passed in.
 */
class DuplicateAccountResolver
{
    /**
     * @param array<int, array<string, mixed>> $accounts Moodle user records (id, username, email, timecreated)
     */
    public function resolve(string $currentEmail, array $accounts): RepairPlan
    {
        $normalizedEmail = mb_strtolower(trim($currentEmail));

        // Dedupe by Moodle id and drop malformed records.
        $byId = [];
        foreach ($accounts as $account) {
            if (empty($account['id'])) {
                continue;
            }

            $byId[(int) $account['id']] = $account;
        }

        if (\count($byId) < 2) {
            return new RepairPlan(RepairStatus::HEALTHY, 'Single Moodle account, nothing to repair.');
        }

        $matchingCurrent = [];
        $others = [];

        foreach ($byId as $id => $account) {
            if ($this->matchesEmail($account, $normalizedEmail)) {
                $matchingCurrent[$id] = $account;
            } else {
                $others[$id] = $account;
            }
        }

        // We only auto-repair the unambiguous shape: exactly one account on the
        // current email (the empty duplicate) and exactly one on the former email.
        if (1 !== \count($matchingCurrent) || 1 !== \count($others)) {
            return new RepairPlan(
                RepairStatus::MANUAL,
                \sprintf('Ambiguous: %d account(s) on the current email, %d other(s) — manual review required.', \count($matchingCurrent), \count($others)),
            );
        }

        $duplicate = reset($matchingCurrent); // empty account that captured the new email
        $original = reset($others);           // account holding the progress, still on the former email

        // The account holding the progress must predate the duplicate; otherwise our assumption is wrong.
        if ((int) ($original['timecreated'] ?? 0) > (int) ($duplicate['timecreated'] ?? 0)) {
            return new RepairPlan(
                RepairStatus::MANUAL,
                'The account on the current email is older than the other one — manual review required.',
            );
        }

        return new RepairPlan(
            RepairStatus::REPAIR,
            \sprintf('Keep #%d (oldest, holds progress), delete empty duplicate #%d, move the email onto the kept account.', (int) $original['id'], (int) $duplicate['id']),
            (int) $original['id'],
            (int) $duplicate['id'],
            $normalizedEmail,
        );
    }

    /**
     * @param array<string, mixed> $account
     */
    private function matchesEmail(array $account, string $normalizedEmail): bool
    {
        foreach (['email', 'username'] as $field) {
            if (isset($account[$field]) && mb_strtolower(trim((string) $account[$field])) === $normalizedEmail) {
                return true;
            }
        }

        return false;
    }
}
