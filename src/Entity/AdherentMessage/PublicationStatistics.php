<?php

declare(strict_types=1);

namespace App\Entity\AdherentMessage;

use App\JeMengage\Hit\Stats\DTO\StatsOutput;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

#[ORM\Entity]
#[ORM\Table(name: 'adherent_message_statistics')]
class PublicationStatistics
{
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private int $id;

    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\OneToOne(inversedBy: 'statistics')]
    public AdherentMessage $message;

    #[Groups(['message_read', 'message_read_list'])]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true, 'default' => 0])]
    public int $contacts = 0;

    #[Groups(['message_read', 'message_read_list'])]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true, 'default' => 0])]
    public int $visibleCount = 0;

    #[Groups(['message_read', 'message_read_list'])]
    #[ORM\Column(type: 'integer', nullable: true, options: ['unsigned' => true])]
    public ?int $uniqueEmails = null;

    #[Groups(['message_read', 'message_read_list'])]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true, 'default' => 0])]
    public int $uniqueNotifications = 0;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true, 'default' => 0])]
    public int $notificationsWeb = 0;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true, 'default' => 0])]
    public int $notificationsIos = 0;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true, 'default' => 0])]
    public int $notificationsAndroid = 0;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true, 'default' => 0])]
    public int $uniqueImpressionsList = 0;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true, 'default' => 0])]
    public int $uniqueImpressionsTimeline = 0;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true, 'default' => 0])]
    public int $uniqueImpressions = 0;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true, 'default' => 0])]
    public int $uniqueOpensApp = 0;

    #[ORM\Column(type: 'float', options: ['unsigned' => true, 'default' => 0])]
    public float $uniqueOpensAppRate = 0;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true, 'default' => 0])]
    public int $uniqueOpensEmail = 0;

    #[ORM\Column(type: 'float', options: ['unsigned' => true, 'default' => 0])]
    public float $uniqueOpensEmailRate = 0;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true, 'default' => 0])]
    public int $uniqueOpensNotification = 0;

    #[ORM\Column(type: 'float', options: ['unsigned' => true, 'default' => 0])]
    public float $uniqueOpensNotificationRate = 0;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true, 'default' => 0])]
    public int $uniqueOpensDirectLink = 0;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true, 'default' => 0])]
    public int $uniqueOpensList = 0;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true, 'default' => 0])]
    public int $uniqueOpensTimeline = 0;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true, 'default' => 0])]
    public int $uniqueOpens = 0;

    #[ORM\Column(type: 'float', options: ['unsigned' => true, 'default' => 0])]
    public float $uniqueOpensRate = 0;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true, 'default' => 0])]
    public int $uniqueClicksApp = 0;

    #[ORM\Column(type: 'float', options: ['unsigned' => true, 'default' => 0])]
    public float $uniqueClicksAppRate = 0;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true, 'default' => 0])]
    public int $uniqueClicksEmail = 0;

    #[ORM\Column(type: 'float', options: ['unsigned' => true, 'default' => 0])]
    public float $uniqueClicksEmailRate = 0;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true, 'default' => 0])]
    public int $uniqueClicks = 0;

    #[ORM\Column(type: 'float', options: ['unsigned' => true, 'default' => 0])]
    public float $uniqueClicksRate = 0;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true, 'default' => 0])]
    public int $unsubscribed = 0;

    #[ORM\Column(type: 'float', options: ['unsigned' => true, 'default' => 0])]
    public float $unsubscribedRate = 0;

    public function __construct(AdherentMessage $message)
    {
        $this->message = $message;
    }

    #[Groups(['message_read', 'message_read_list'])]
    public function getSentAt(): ?\DateTimeInterface
    {
        return $this->message->getSentAt();
    }

    #[Groups(['message_read', 'message_read_list'])]
    #[SerializedName('notifications')]
    public function getNotificationsData(): array
    {
        return [
            'android' => $this->notificationsAndroid,
            'ios' => $this->notificationsIos,
            'web' => $this->notificationsWeb,
        ];
    }

    #[Groups(['message_read', 'message_read_list'])]
    #[SerializedName('unique_impressions')]
    public function getUniqueImpressionsData(): array
    {
        return [
            'list' => $this->uniqueImpressionsList,
            'timeline' => $this->uniqueImpressionsTimeline,
            'total' => $this->uniqueImpressions,
        ];
    }

    #[Groups(['message_read', 'message_read_list'])]
    #[SerializedName('unique_opens')]
    public function getUniqueOpensData(): array
    {
        return [
            'app' => $this->uniqueOpensApp,
            'app_rate' => $this->uniqueOpensAppRate,
            'direct_link' => $this->uniqueOpensDirectLink,
            'email' => $this->uniqueOpensEmail,
            'email_rate' => $this->uniqueOpensEmailRate,
            'list' => $this->uniqueOpensList,
            'notification' => $this->uniqueOpensNotification,
            'notification_rate' => $this->uniqueOpensNotificationRate,
            'timeline' => $this->uniqueOpensTimeline,
            'total' => $this->uniqueOpens,
            'total_rate' => $this->uniqueOpensRate,
        ];
    }

    #[Groups(['message_read', 'message_read_list'])]
    #[SerializedName('unique_clicks')]
    public function getUniqueClicksData(): array
    {
        return [
            'app' => $this->uniqueClicksApp,
            'app_rate' => $this->uniqueClicksAppRate,
            'email' => $this->uniqueClicksEmail,
            'email_rate' => $this->uniqueClicksEmailRate,
            'total' => $this->uniqueClicks,
            'total_rate' => $this->uniqueClicksRate,
        ];
    }

    #[Groups(['message_read', 'message_read_list'])]
    #[SerializedName('unsubscribed')]
    public function getUnsubscribedData(): array
    {
        return [
            'total' => $this->unsubscribed,
            'total_rate' => $this->unsubscribedRate,
        ];
    }

    public function refresh(StatsOutput $stats): void
    {
        $mapping = [
            'contacts' => 'contacts',
            'visible_count' => 'visibleCount',
            'unique_notifications' => 'uniqueNotifications',
            'unique_emails' => 'uniqueEmails',

            'notifications__web' => 'notificationsWeb',
            'notifications__ios' => 'notificationsIos',
            'notifications__android' => 'notificationsAndroid',

            'unique_impressions__list' => 'uniqueImpressionsList',
            'unique_impressions__timeline' => 'uniqueImpressionsTimeline',
            'unique_impressions' => 'uniqueImpressions',

            'unique_opens__app' => 'uniqueOpensApp',
            'unique_opens__app_rate' => 'uniqueOpensAppRate',
            'unique_opens__email' => 'uniqueOpensEmail',
            'unique_opens__email_rate' => 'uniqueOpensEmailRate',
            'unique_opens__notification' => 'uniqueOpensNotification',
            'unique_opens__notification_rate' => 'uniqueOpensNotificationRate',
            'unique_opens__direct_link' => 'uniqueOpensDirectLink',
            'unique_opens__list' => 'uniqueOpensList',
            'unique_opens__timeline' => 'uniqueOpensTimeline',
            'unique_opens' => 'uniqueOpens',
            'unique_opens__total_rate' => 'uniqueOpensRate',

            'unique_clicks__app' => 'uniqueClicksApp',
            'unique_clicks__app_rate' => 'uniqueClicksAppRate',
            'unique_clicks__email' => 'uniqueClicksEmail',
            'unique_clicks__email_rate' => 'uniqueClicksEmailRate',
            'unique_clicks' => 'uniqueClicks',
            'unique_clicks__total_rate' => 'uniqueClicksRate',

            'unsubscribed' => 'unsubscribed',
            'unsubscribed__total_rate' => 'unsubscribedRate',
        ];

        foreach ($mapping as $sourceKey => $targetProperty) {
            $value = $stats->get($sourceKey);

            if (null === $value) {
                if (!str_contains($sourceKey, '__') && \in_array($sourceKey, ['unique_impressions', 'unique_opens', 'unique_clicks', 'unsubscribed'])) {
                    $value = $stats->get($sourceKey.'__total');
                }

                if (null === $value) {
                    continue;
                }
            }

            if (str_ends_with($targetProperty, 'Rate')) {
                $this->$targetProperty = (float) $value;
            } else {
                $this->$targetProperty = (int) $value;
            }
        }
    }
}
