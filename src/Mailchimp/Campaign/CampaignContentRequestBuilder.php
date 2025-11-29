<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign;

use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Mailchimp\Campaign\Request\EditCampaignContentRequest;

class CampaignContentRequestBuilder
{
    private $objectIdMapping;
    /**
     * @var CampaignContentRequestBuilder[]|iterable
     */
    private $builders;

    public function __construct(MailchimpObjectIdMapping $objectIdMapping, iterable $builders)
    {
        $this->objectIdMapping = $objectIdMapping;
        $this->builders = $builders;
    }

    public function createContentRequest(AdherentMessageInterface $message): EditCampaignContentRequest
    {
        $request = new EditCampaignContentRequest(
            $this->objectIdMapping->getTemplateId($message),
            $message->getContent()
        );

        foreach ($this->builders as $builder) {
            if ($builder->supports($message)) {
                $builder->build($message, $request);
            }
        }

        return $request;
    }
}
