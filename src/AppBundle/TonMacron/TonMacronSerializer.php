<?php

namespace AppBundle\TonMacron;

use AppBundle\Entity\TonMacronChoice;
use AppBundle\Entity\TonMacronFriendInvitation;

class TonMacronSerializer
{
    /**
     * @param TonMacronChoice[] $choices
     *
     * @return string
     */
    public function serializeChoices(array $choices): string
    {
        if (!is_iterable($choices)) {
            throw new \InvalidArgumentException();
        }

        $handle = fopen('php://memory', 'rb+');
        fputcsv($handle, [
            'id',
            'key',
            'label',
            'content',
        ]);

        foreach ($choices as $choice) {
            fputcsv($handle, [
                'id' => $choice->getId(),
                'key' => $choice->getContentKey(),
                'label' => $choice->getLabel(),
                'content' => $choice->getContent(),
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }

    /**
     * @param TonMacronFriendInvitation[] $invitations
     *
     * @return string
     */
    public function serializeInvitations(array $invitations): string
    {
        if (!is_iterable($invitations)) {
            throw new \InvalidArgumentException();
        }

        $handle = fopen('php://memory', 'rb+');
        fputcsv($handle, [
            'id',
            'friend_firstName',
            'friend_age',
            'friend_gender',
            'friend_position',
            'friend_emailAddress',
            'author_firstName',
            'author_lastName',
            'author_emailAddress',
            'mail_subject',
            'date',
            'choices',
        ]);

        foreach ($invitations as $invitation) {
            fputcsv($handle, [
                'id' => $invitation->getId(),
                'friend_firstName' => $invitation->getFriendFirstName(),
                'friend_age' => $invitation->getFriendAge(),
                'friend_gender' => $invitation->getFriendGender(),
                'friend_position' => $invitation->getFriendPosition(),
                'friend_emailAddress' => $invitation->getFriendEmailAddress(),
                'author_firstName' => $invitation->getAuthorFirstName(),
                'author_lastName' => $invitation->getAuthorLastName(),
                'author_emailAddress' => $invitation->getAuthorEmailAddress(),
                'mail_subject' => $invitation->getMailSubject(),
                'date' => $invitation->getCreatedAt()->format('Y-m-d H:i:s'),
                'choices' => implode(',', array_map(function (TonMacronChoice $choice) {
                    return $choice->getContentKey();
                }, $invitation->getChoices()->toArray())),
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }
}
