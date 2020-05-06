<?php

namespace App\Normalizer;

use App\Newsletter\Command\MailchimpSyncSiteNewsletterCommand;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class DptSiteNewsletterCommandDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = [])
    {
        return $this->denormalizer->denormalize($data, MailchimpSyncSiteNewsletterCommand::class, $format, $context);
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return 'en-marche_app_mailchimp_site_newsletter_sync_command' === $type;
    }
}
