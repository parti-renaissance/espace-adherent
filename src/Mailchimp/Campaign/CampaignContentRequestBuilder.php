<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign;

use App\AdherentMessage\Variable\Renderer;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Mailchimp\Campaign\ContentSection\ContentSectionBuilderInterface;
use App\Mailchimp\Campaign\Request\EditCampaignContentRequest;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

class CampaignContentRequestBuilder
{
    /**
     * @var ContentSectionBuilderInterface[]
     */
    public function __construct(
        private readonly MailchimpObjectIdMapping $objectIdMapping,
        private readonly Renderer $variableRenderer,
        #[TaggedIterator('app.mailchimp.campaign.content_builder')]
        private readonly iterable $builders,
    ) {
    }

    public function createContentRequest(AdherentMessageInterface $message): EditCampaignContentRequest
    {
        $request = new EditCampaignContentRequest(
            $this->objectIdMapping->getTemplateId($message),
            $this->variableRenderer->renderMailchimp($message->getContent())
        );

        if (AdherentMessageInterface::SOURCE_CADRE === $message->getSource()) {
            foreach ($this->builders as $builder) {
                if ($builder->supports($message)) {
                    $builder->build($message, $request);
                }
            }
        }

        return $request;
    }
}
