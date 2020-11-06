<?php

namespace App\Controller\EnMarche;

use App\Entity\AbstractFeedItem;
use App\FeedItem\FeedItemPermissions;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

trait FeedItemControllerTrait
{
    public function deleteFeedItem(
        EntityManagerInterface $em,
        Request $request,
        AbstractFeedItem $feedItem,
        string $feedItemType
    ): void {
        $form = $this->createDeleteForm('', "{$feedItemType}_delete_feed_item", $request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            throw $this->createNotFoundException($form->isValid() ? 'Invalid token.' : 'No form submitted.');
        }

        $em->remove($feedItem);
        $em->flush();

        $this->addFlash('info', 'common.message_deleted');
    }

    /**
     * @param AbstractFeedItem[]|iterable $feeds
     */
    protected function createFeedItemDeleteForms(iterable $feeds, string $feedItemType): array
    {
        $forms = [];
        foreach ($feeds as $feed) {
            if ($this->isGranted(FeedItemPermissions::CAN_MANAGE, $feed)) {
                $forms[$feed->getId()] = $this->createDeleteForm(
                    $this->generateUrl("app_{$feedItemType}_delete_feed_item",
                        [
                            'id' => $feed->getId(),
                        ]),
                    "{$feedItemType}_delete_feed_item"
                )->createView()
                ;
            }
        }

        return $forms;
    }
}
