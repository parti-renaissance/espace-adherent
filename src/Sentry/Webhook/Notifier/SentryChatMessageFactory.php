<?php

declare(strict_types=1);

namespace App\Sentry\Webhook\Notifier;

use App\ClickUp\Notifier\ClickUpOptions;
use App\Sentry\Webhook\Routing\RoutingDecision;
use App\Sentry\Webhook\SentryEvent;
use Symfony\Component\Notifier\Bridge\Slack\Block\SlackSectionBlock;
use Symfony\Component\Notifier\Bridge\Slack\SlackOptions;
use Symfony\Component\Notifier\Message\ChatMessage;

class SentryChatMessageFactory
{
    /**
     * @return ChatMessage[]
     */
    public function create(SentryEvent $event, RoutingDecision $decision): array
    {
        $messages = [];

        if (null !== $decision->slackChannelId) {
            $messages[] = $this->slackMessage($event, $decision);
        }

        if (null !== $decision->clickUpChannelId) {
            $messages[] = $this->clickUpMessage($event, $decision);
        }

        return $messages;
    }

    private function slackMessage(SentryEvent $event, RoutingDecision $decision): ChatMessage
    {
        $level = $event->level ?? 'error';
        $title = $this->escape(mb_strimwidth($event->title, 0, 250, '…'));

        $headline = null !== $event->webUrl
            ? \sprintf('%s *<%s|%s>*', $this->levelEmoji($level), $event->webUrl, $title)
            : \sprintf('%s *%s*', $this->levelEmoji($level), $title);

        if (null !== $event->culprit) {
            $headline .= "\n`".$this->escape($event->culprit).'`';
        }

        $options = new SlackOptions()
            ->recipient((string) $decision->slackChannelId)
            ->block(new SlackSectionBlock()->text($headline))
        ;

        return new ChatMessage($this->title($event, $decision), $options)->transport('slack');
    }

    private function clickUpMessage(SentryEvent $event, RoutingDecision $decision): ChatMessage
    {
        $emoji = $this->levelEmoji($event->level ?? 'error');
        $title = mb_strimwidth($event->title, 0, 250, '…');

        $content = null !== $event->webUrl
            ? \sprintf('%s **[%s](%s)**', $emoji, $title, $event->webUrl)
            : \sprintf('%s **%s**', $emoji, $title);

        if (null !== $event->culprit) {
            $content .= "\n`".$event->culprit.'`';
        }

        return new ChatMessage($content, new ClickUpOptions((string) $decision->clickUpChannelId))->transport('clickup');
    }

    private function title(SentryEvent $event, RoutingDecision $decision): string
    {
        return \sprintf('[%s] %s', $decision->category, $event->title);
    }

    private function levelEmoji(string $level): string
    {
        return match (mb_strtolower($level)) {
            'fatal' => '🚨',
            'warning' => '🟠',
            'info' => '🔵',
            'debug' => '⚪',
            default => '🔴',
        };
    }

    private function escape(string $text): string
    {
        return str_replace(['&', '<', '>', '|'], ['&amp;', '&lt;', '&gt;', '/'], $text);
    }
}
