<?php

declare(strict_types=1);

namespace App\Validator\Email;

use App\Validator\Email\Reason\EmailTypoReason;
use Egulias\EmailValidator\EmailLexer;
use Egulias\EmailValidator\Result\InvalidEmail;
use Egulias\EmailValidator\Validation\EmailValidation;

/**
 * Suggest corrections for common email-domain typos (port of Kicksend's mailcheck.js).
 *
 * Two-stage algorithm:
 * 1. Full-domain Levenshtein against POPULAR_DOMAINS (threshold 2): catches gmai.com → gmail.com.
 * 2. TLD-only correction, conditioned on the host label being a popular label: catches gmail.con → gmail.com,
 *    while preventing false positives on legitimate but unknown labels (example.ai stays as is).
 */
class EmailTypoValidation implements EmailValidation
{
    private const POPULAR_DOMAINS = [
        // Google
        'gmail.com', 'googlemail.com',
        // Microsoft
        'outlook.com', 'outlook.fr',
        'hotmail.com', 'hotmail.fr', 'hotmail.co.uk',
        'live.fr', 'live.com', 'msn.com',
        // Apple
        'icloud.com', 'me.com',
        // Yahoo
        'yahoo.com', 'yahoo.fr', 'yahoo.co.uk', 'ymail.com', 'rocketmail.com',
        // AOL
        'aol.com', 'aol.fr',
        // GMX
        'gmx.fr', 'gmx.com',
        // Privacy mainstream
        'protonmail.com',
        // FAI FR actifs
        'free.fr', 'orange.fr', 'sfr.fr', 'laposte.net',
        'wanadoo.fr', 'bbox.fr', 'neuf.fr',
        // FAI FR historiques (count significatif dans la base)
        'club-internet.fr', 'numericable.fr', 'cegetel.net',
    ];

    private const POPULAR_TLDS = ['com', 'fr', 'net', 'org', 'eu', 'io', 'be', 'ch'];

    /** Host labels (part before the first dot) of POPULAR_DOMAINS, used by stage 2 to gate TLD-only suggestions. */
    private const POPULAR_LABELS = [
        'gmail', 'googlemail',
        'outlook', 'hotmail', 'live', 'msn',
        'icloud', 'me',
        'yahoo', 'ymail', 'rocketmail',
        'aol', 'gmx', 'protonmail',
        'free', 'orange', 'sfr', 'laposte', 'wanadoo', 'bbox', 'neuf',
        'club-internet', 'numericable', 'cegetel',
    ];

    private const DOMAIN_THRESHOLD = 2;
    private const TLD_THRESHOLD = 1;
    private const MAX_EMAIL_LENGTH = 254;

    private ?InvalidEmail $error = null;

    public function isValid(string $email, EmailLexer $emailLexer): bool
    {
        $suggestion = $this->suggest($email);

        if (null !== $suggestion) {
            $this->error = new InvalidEmail(new EmailTypoReason($suggestion), '');
        }

        return null === $this->error;
    }

    public function getError(): ?InvalidEmail
    {
        return $this->error;
    }

    public function getWarnings(): array
    {
        return [];
    }

    private function suggest(string $email): ?string
    {
        $email = mb_strtolower($email);

        if (mb_strlen($email) > self::MAX_EMAIL_LENGTH) {
            return null;
        }

        $atPos = strrpos($email, '@');
        if (false === $atPos || 0 === $atPos || $atPos === mb_strlen($email) - 1) {
            return null;
        }

        $local = substr($email, 0, $atPos);
        $host = substr($email, $atPos + 1);

        $dotPos = strrpos($host, '.');
        if (false === $dotPos || 0 === $dotPos || $dotPos === mb_strlen($host) - 1) {
            return null;
        }

        $domain = substr($host, 0, $dotPos);
        $tld = substr($host, $dotPos + 1);

        if (\in_array($host, self::POPULAR_DOMAINS, true)) {
            return null;
        }

        // Stage 1: whole-domain typo against the popular-domains list.
        $bestDomain = $this->closest($host, self::POPULAR_DOMAINS, self::DOMAIN_THRESHOLD);
        if (null !== $bestDomain) {
            return $local.'@'.$bestDomain;
        }

        // Stage 2: TLD-only typo. Only triggered when the host label itself is popular,
        // to avoid false positives on legitimate unknown labels (example.ai must stay null).
        if (\in_array($domain, self::POPULAR_LABELS, true) && !\in_array($tld, self::POPULAR_TLDS, true)) {
            $bestTld = $this->closest($tld, self::POPULAR_TLDS, self::TLD_THRESHOLD);
            if (null !== $bestTld && $bestTld !== $tld) {
                return $local.'@'.$domain.'.'.$bestTld;
            }
        }

        return null;
    }

    /**
     * @param string[] $candidates
     */
    private function closest(string $needle, array $candidates, int $threshold): ?string
    {
        $bestDistance = $threshold + 1;
        $bestMatch = null;

        foreach ($candidates as $candidate) {
            $distance = levenshtein($needle, $candidate);
            if ($distance < 0) {
                continue;
            }
            if ($distance > 0 && $distance < $bestDistance) {
                $bestDistance = $distance;
                $bestMatch = $candidate;
            }
        }

        return $bestMatch;
    }
}
