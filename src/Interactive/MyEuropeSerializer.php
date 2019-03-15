<?php

namespace AppBundle\Interactive;

use AppBundle\Entity\MyEuropeChoice;
use AppBundle\Entity\MyEuropeInvitation;

class MyEuropeSerializer
{
    /**
     * @param MyEuropeChoice[] $choices
     */
    public function serializeChoices(array $choices): string
    {
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
     * @param MyEuropeInvitation[] $invitations
     */
    public function serializeInvitations(array $invitations): string
    {
        $handle = fopen('php://memory', 'rb+');
        foreach ($invitations as $invitation) {
            fputcsv($handle, [
                'id' => $invitation->getId(),
                'friend_age' => $invitation->getFriendAge(),
                'friend_gender' => $invitation->getFriendGender(),
                'friend_position' => $invitation->getFriendPosition(),
                'friend_emailAddress' => $invitation->getFriendEmailAddress(),
                'author_firstName' => $invitation->getAuthorFirstName(),
                'author_emailAddress' => $invitation->getAuthorEmailAddress(),
                'mail_subject' => $invitation->getMailSubject(),
                'date' => $invitation->getCreatedAt()->format('Y-m-d H:i:s'),
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }
}
