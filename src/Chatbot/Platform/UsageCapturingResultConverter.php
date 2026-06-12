<?php

declare(strict_types=1);

namespace App\Chatbot\Platform;

use App\Chatbot\Usage\ChatbotUsageTracker;
use Symfony\AI\Platform\Bridge\Generic\Completions\ResultConverter;
use Symfony\AI\Platform\Result\RawHttpResult;
use Symfony\AI\Platform\Result\RawResultInterface;
use Symfony\AI\Platform\Result\ResultInterface;
use Symfony\AI\Platform\TokenUsage\TokenUsage;

class UsageCapturingResultConverter extends ResultConverter
{
    public function __construct(private readonly ChatbotUsageTracker $usageTracker)
    {
    }

    public function convert(RawResultInterface|RawHttpResult $result, array $options = []): ResultInterface
    {
        $converted = parent::convert($result, $options);

        if (!($options['stream'] ?? false)) {
            $data = $result->getData();

            if (isset($data['usage'])) {
                $this->usageTracker->capture($data['usage']);
            }
        }

        return $converted;
    }

    protected function convertStreamUsage(array $usage): TokenUsage
    {
        $this->usageTracker->capture($usage);

        return parent::convertStreamUsage($usage);
    }
}
