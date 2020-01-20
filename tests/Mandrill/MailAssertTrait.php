<?php

namespace Tests\AppBundle\Mandrill;

use AppBundle\Entity\Email;

trait MailAssertTrait
{
    public static function assertMailSubject(string $expectedSubject, Email $email): void
    {
        self::assertSame($expectedSubject, $email->getRequestPayload()['message']['subject']);
    }

    public static function assertMailFromName(string $expectedFromName, Email $email): void
    {
        self::assertSame($expectedFromName, $email->getRequestPayload()['message']['from_name']);
    }

    public static function assertMailTemplateName(string $expectedTemplateName, Email $email): void
    {
        self::assertSame($expectedTemplateName, $email->getRequestPayload()['template_name']);
    }

    public static function assertMailVars(array $expectedVars, Email $email): void
    {
        self::assertSame(
            $expectedVars,
            array_merge(...array_map(
                static function (array $var) { return [$var['name'] => $var['content']]; },
                $email->getRequestPayload()['message']['global_merge_vars']
            ))
        );
    }
}
